<?php
namespace Lead\Validator;

use Closure;
use InvalidArgumentException;
use Lead\Set\Set;
use Lead\Text\Text;

/**
 * The `Validator` class provides the necessary logic to perform some validation on data.
 *
 * Example:
 * {{{
 * use validator\Validator;
 *
 * $validator = new Validator();
 * $validator->rule('title', [
 *     'not:empty' => ['message' => 'please enter a title']
 * ]);
 * $validator->validate(['title' => 'new title']); // true
 * }}}
 *
 * @see Checker class for all built-in rules
 */
class Validator {

    /**
     * Class dependencies.
     *
     * @var array
     */
    protected $_classes = [
        'checker' => 'Lead\Validator\Checker'
    ];

    /**
     * Some optionnal meta data.
     *
     * @var string
     */
    protected $_meta = [];

    /**
     * Local validation handlers.
     *
     * @var array
     */
    protected $_handlers = [];

    /**
     * The validation rules.
     *
     * @var array
     */
    protected $_rules = [];

    /**
     * The logged errors.
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * The error messages.
     *
     * @var Closure
     */
    protected $_messages = [];

    /**
     * The error message handler.
     *
     * @var Closure
     */
    protected $_error = null;

    /**
     * Constructor
     *
     * @param array $config The config array. Possible values are:
     *                      - `'handlers'` _array_ : Some custom handlers.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'meta'     => [],
            'handlers' => [],
            'classes'  => $this->_classes,
            'error'    => function($name, $options, $meta = []) {
                return Text::insert($options['message'] ?: $this->message($name), $options);
            }
        ];

        $config = Set::merge($defaults, $config);

        $this->_classes = $config['classes'];
        $this->set($config['handlers']);
        $this->error($config['error']);
        $this->meta($config['meta']);
    }

    /**
     * Sets one or several validation rules.
     *
     * For example:
     * {{{
     * $validator = new Validator();
     * $validator->set('zeroToNine', '/^[0-9]$/');
     * $isValid = $validator->isZeroToNine('5'); // true
     * $isValid = $validator->isZeroToNine('20'); // false
     * }}}
     *
     * Alternatively, the first parameter may be an array of rules expressed as key/value pairs,
     * as in the following:
     * {{{
     * $validator = new Validator();
     * $validator->set([
     *  'zeroToNine' => '/^[0-9]$/',
     *  'tenToNineteen' => '/^1[0-9]$/',
     * ]);
     * }}}
     *
     * In addition to regular expressions, validation rules can also be defined as full anonymous
     * functions:
     * {{{
     * use app\model\Account;
     *
     * $validator = new Validator();
     * $validator->set('accountActive', function($value, $options = []) {
     *   $value = is_int($value) ? Account::id($value) : $value;
     *   return (boolean) $value->is_active;
     * });
     *
     * $testAccount = Account::create(['is_active' => false]);
     * $validator->isAccountActive($testAccount); // returns false
     * }}}
     *
     * These functions can take up to 3 parameters:
     *  - `$value`  _mixed_ : This is the actual value to be validated (as in the above example).
     *  - `$options` _array_: This parameter allows a validation rule to implement custom options.
     *                        - `'format'` _string_: Often, validation rules come in multiple "formats", for example:
     *                           postal codes, which vary by country or region. Defining multiple formats allows you to
     *                           retain flexibility in how you validate data. In cases where a user's country of origin
     *                           is known, the appropriate validation rule may be selected. In cases where it is not
     *                           known, the value of `$format` may be `'any'`, which should pass if any format matches.
     *                           In cases where validation rule formats are not mutually exclusive, the value may be
     *                           `'all'`, in which case all must match.

     *
     * @param mixed  $name The name of the validation rule (string), or an array of key/value pairs
     *                     of names and rules.
     * @param string $rule If $name is a string, this should be a string regular expression, or a
     *                     closure that returns a boolean indicating success. Should be left blank if
     *                     `$name` is an array.
     */
    public function set($name, $rule = null)
    {
        if (!is_array($name)) {
            $name = [$name => $rule];
        }
        $this->_handlers = $name + $this->_handlers;
    }

    /**
     * Checks if a validation handler exists.
     *
     * @param string $name A validation handler name.
     */
    public function has($name)
    {
        $checker = $this->_classes['checker'];
        return isset($this->_handlers[$name]) || $checker::has($name);
    }

    /**
     * Returns a validation handler.
     *
     * @param string $name A validation handler name.
     */
    public function get($name)
    {
        if (isset($this->_handlers[$name])) {
           return $this->_handlers[$name];
        }
        $checker = $this->_classes['checker'];
        if ($checker::has($name)) {
           return $checker::get($name);
        }
        throw new InvalidArgumentException("Unexisting `{$name}` as validation handler.");
    }

    /**
     * Gets/sets the available validation handlers.
     *
     * @param  array   $handlers The handlers to set.
     * @param  boolean $append   Indicating if the handlers need to be appended or replaced.
     * @return array             The list of available validation handlers
     */
    public function handlers($handlers = [], $append = true)
    {
        if (!func_num_args()) {
            $checker = $this->_classes['checker'];
            return $this->_handlers + $checker::handlers();
        }
        if ($append) {
            $this->_handlers = array_merge($this->_handlers, $handlers);
        } else {
            $this->_handlers = $handlers;
        }
        return $this->handlers();
    }

    /**
     * Sets a rule.
     *
     * @param mixed  $name    A fieldname.
     * @param string $handler A validation handler name.
     */
    public function rule($field, $rules = [])
    {
        $defaults = [
            'message' => null,
            'required' => true,
            'skipEmpty' => false,
            'format' => 'any',
            'not' => false,
            'on' => null
        ];

        $rules = $rules ? (array) $rules : [];

        foreach ($rules as $name => $options) {
            if (is_numeric($name)) {
                $name = $options;
                $options = [];
            }
            if (is_string($options)) {
                $options = ['message' => $options];
            }
            $options += $defaults;
            $this->_rules[$field][$name] = $options;
        }
    }

    /**
     * Validates a set of values against a specified rules list. This method may be used to validate
     * any arbitrary array of data against a set of validation rules.
     *
     * @param array $data    An array of key/value pairs, where the values are to be checked.
     * @param array $rules   An array of rules to check the values in `$values` against. Each key in
     *                       `$rules` should match a key contained in `$values`, and each value should be a
     *                       validation rule in one of the allowable formats. For example, if you are
     *                       validating a data set containing a `'credit_card'` key, possible values for
     *                       `$rules` would be as follows:
     *                       - `['credit_card' => 'You must include a credit card number']`: This is the
     *                         simplest form of validation rule, in which the value is simply a message to
     *                         display if the rule fails. Using this format, all other validation settings
     *                         inherit from the defaults, including the validation rule itself, which only
     *                         checks to see that the corresponding key in `$values` is present and contains
     *                         a value that is not empty. _Please note when globalizing validation messages:_
     *                         When specifying messages, it may be preferable to use a code string (i.e.
     *                         `'ERR_NO_TITLE'`) instead of the full text of the validation error. These code
     *                         strings may then be translated by the appropriate tools in the templating layer.
     *                       - `['credit_card' => ['creditCard', 'message' => 'Invalid CC #']]`:
     *                         In the second format, the validation rule (in this case `creditCard`) and
     *                         associated configuration are specified as an array, where the rule to use is
     *                         the first value in the array (no key), and additional settings are specified
     *                         as other keys in the array. Please see the list below for more information on
     *                         allowed keys.
     *                       - The final format allows you to apply multiple validation rules to a single
     *                         value, and it is specified as follows:
     *                         `['credit_card' => [
     *                              ['not:empty', 'message' => 'You must include credit card number'],
     *                              ['creditCard', 'message' => 'Your credit card number must be valid']
     *                         ]];`
     * @param array $options Validator-specific options.
     *                       Each rule defined as an array can contain any of the following settings
     *                       (in addition to the first value, which represents the rule to be used):
     *                       - `'message'` _string_: The error message to be returned if the validation
     *                         rule fails. See the note above regarding globalization of error messages.
     *                       - `'required`' _boolean_: Represents whether the value is required to be
     *                         present in `$values`. If `'required'` is set to `false`, the validation rule
     *                         will be skipped if the corresponding key is not present. Defaults to `true`.
     *                       - `'skipEmpty'` _boolean_: Similar to `'required'`, this setting (if `true`)
     *                         will cause the validation rule to be skipped if the corresponding value
     *                         is empty (an empty string or `null`). Defaults to `false`.
     *                       - `'format'` _string_: If the validation rule has multiple format definitions
     *                         (see the `add()` or `init()` methods), the name of the format to be used
     *                         can be specified here. Additionally, two special values can be used:
     *                         either `'any'`, which means that all formats will be checked and the rule
     *                         will pass if any format passes, or `'all'`, which requires all formats to
     *                         pass in order for the rule check to succeed.
     * @return array           Returns an array containing all validation failures for data in `$values`,
     *                         where each key matches a key in `$values`, and each value is an array of
     *                         that element's validation errors.
     */
    public function validate($data, $options = [])
    {
        $events = (array) (isset($options['events']) ? $options['events'] : null);

        $this->_errors = [];
        $error = $this->_error;

        foreach ($this->_rules as $field => $rules) {
            $values = static::values($data, explode('.', $field));

            foreach ($rules as $name => $rule) {
                $rule += $options;
                $rule['field'] = $field;

                if ($events && $rule['on'] && !array_intersect($events, (array) $rule['on'])) {
                    continue;
                }

                if (!$values && $rule['required']) {
                    $rule['message'] = null;
                    $this->_errors[$field][] = $error('required', $rule, $this->_meta);
                    break;
                } else {
                    foreach ($values as $key => $value) {
                        if (empty($value) && $rule['skipEmpty']) {
                            continue;
                        }
                        if (!$this->is($name, $value, $rule + compact('data'), $params)) {
                            $this->_errors[$key][] = $error($name, $params + $rule, $this->_meta);
                        }
                    }
                }
            }
        }
        return !$this->_errors;
    }

    /**
     * Returns the errors from the last validate call.
     *
     * @return array The occured errors.
     */
    public function errors()
    {
        return $this->_errors;
    }

    /**
     * Checks a single value against a validation handler.
     *
     * @param  string  $rule    The validation handler name.
     * @param  mixed   $value   The value to check.
     * @param  array   $options The options array.
     * @return boolean          Returns `true` or `false` indicating whether the validation rule check
     *                          succeeded or failed.
     */
    public function is($name, $value, $options = [], &$params = [])
    {
        $not = false;
        if (strncmp($name, 'not:', 4) === 0) {
            $name = substr($name, 4);
            $not = true;
        }
        $handlers = $this->get($name);
        $handlers = is_array($handlers) ? $handlers : [$handlers];
        $checker = $this->_classes['checker'];

        return $checker::check($value, $handlers, $options, $params) !== $not;
    }

    /**
     * Maps method calls to validation rule names. For example, a validation rule that would
     * normally be called as `$validator->is('email', 'foo@bar.com')` can also be called as
     * `$validator->isEmail('foo@bar.com')`.
     *
     * @param  string  $method The name of the method called.
     * @param  array   $params The passed parameters
     * @return boolean
     */
    public function __call($method, $params = [])
    {
        if (!isset($params[0])) {
            return false;
        }
        $name = preg_replace_callback('~is(Not)?([A-Z][A-Za-z0-9]+)$~', function($matches) {
            $name = lcfirst($matches[2]);
            return $matches[1] ? 'not:' . $name : $name;
        }, $method);
        return $this->is($name, $params[0], isset($params[1]) ?  $params[1] : []);
    }

    /**
     * Extracts all values corresponding to a field names path.
     *
     * @param  array $data The data.
     * @param  array $path An array of field names.
     * @param  array $base The dotted fielname path of the data.
     * @return array       The extracted values.
     */
    public static function values($data, $path = [], $base = null)
    {
        if (!$path) {
            $base = $base ?: 0;
            return [$base => $data];
        }

        $field = array_shift($path);

        if ($field === '*') {
            $values = [];
            foreach ($data as $key => $value) {
                $values = array_merge($values, static::values($value, $path, $base . '.' . $key));
            }
            return $values;
        } elseif (!isset($data[$field])) {
            return [];
        } elseif (!$path) {
            return [$base ? $base . '.' . $field : $field => $data[$field]];
        } else {
            return static::values($data[$field], $path, $base ? $base . '.' . $field : $field);
        }
    }

    /**
     * Gets/sets a particular error message.
     *
     * @param  string $name    A validation handler name.
     * @param  string $message The validation handler message to set or none to get it.
     * @return                 The validation handler message.
     */
    public function message($name, $message = null)
    {
        if (func_num_args() === 2) {
            return $this->_messages[$name] = $message;
        }
        $checker = $this->_classes['checker'];
        return isset($this->_messages[$name]) ? $this->_messages[$name] : $checker::message($name);
    }

    /**
     * Gets/sets error messages.
     *
     * @param  array $messages The error message array to set or none to get the setted ones.
     * @return array           The error messages.
     */
    public function messages($messages = [], $append = true)
    {
        if (func_num_args()) {
            $this->_messages = $append ? array_merge($this->_messages, $messages) : $messages;
        }
        $checker = $this->_classes['checker'];
        return $this->_messages + $checker::messages();
    }

    /**
     * Gets/sets the error message handler.
     *
     * @param  string $handler The error message handler to set.
     * @return                 The error message handler.
     */
    public function error($handler = null)
    {
        if (func_num_args()) {
            return $this->_error = $handler;
        }
        return $this->_error;
    }

    /**
     * Gets/sets the validator meta data.
     *
     * @param  string $meta The validator meta data to set.
     * @return              The validator meta data.
     */
    public function meta($meta = [])
    {
        if (func_num_args()) {
            return $this->_meta = $meta;
        }
        return $this->_meta;
    }
}
