<?php
namespace validator;

use InvalidArgumentException;
use Closure;
use DateTime;

/**
 * The `Checker` class provides static access to commonly used data validation logic.
 *
 * ## Rules (at class level)
 *
 * The `Checker` class includes a series of commonly-used rules by default, any of which may be
 * used in calls to `is()` or called directly as a method statically or throw an instance.
 * Additionally, rules can have a variety of different _formats_ in which they may be specified.
 *
* Example:
 * {{{
 * use chaos\model\Checker;
 *
 * Checker::is('email', 'foo@example.com');
 * Checker::isEmail('foo@example.com');
 * }}}
 *
 * The following is the list of the built-in rules, but keep in mind that any rule may be
 * overridden by adding a new rule of the same name using the `::set()` method.
 *
 * - `accepted`: Checks that the value is or looks like a boolean value. The following types of
 *   values are interpreted as boolean and will pass the check.
 *   - boolean (`true`, `false`, `'true'`, `'false'`)
 *   - boolean number (`1`, `0`, `'1'`, `'0'`)
 *   - boolean text string (`'on'`, `'off'`, `'yes'`, `'no'`)
 *
 * - `alphaNumeric`: Checks that a string contains only integer or letters.
 *
 * - `boolean`: Checks that the value is or looks like a boolean value. The following types of
 *   values are interpreted as boolean and will pass the check (`true`, `false`, `0`, `1`, `'0'`, `'1'`)
 *
 * - `creditCard`: Checks that a value is a valid credit card number. This rule is divided into a
 *   series of formats: `'amex'`, `'bankcard'`, `'diners'`, `'disc'`, `'electron'`, `'enroute'`,
 *   `'jcb'`, `'maestro'`, `'mc'`, `'solo'`, `'switch'`, `'visa'`, `'voyager'`, `'fast'`. If no
 *   format value is specified, the value defaults to `'any'`, which will validate the value if
 *   _any_ of the available formats match. You can also use the `'fast'` format, which does a
 *   high-speed, low-fidelity check to ensure that the value looks like a real credit card number.
 *   This rule includes one option, `'deep'`, which (if set to `true`) validates the value using the
 *   [Luhn algorithm](http://en.wikipedia.org/wiki/Luhn_algorithm) if the format validation is
 *   successful. See the `luhn` validator below for more details.
 *
 * - `date`: Checks that a value is a valid date.
 *
 * - `dateBefore`: Checks that a date is greater than given date. The available option is `'date'`,
 *   which designate the minimum required date.
 *
 * - `dateAfter`: Checks that a date is lower than given date. The available options is `'date'`,
 *   which designate the maximum required date.
 *
 * - `dateFormat`: Checks that a value is a valid date that complies with one or more formats.
 *   Any kind of Format accepted by DateTime::createFromFormat() can be used, defaults to `'Y-m-d H:i:s'`.
 *
 * - `decimal`: Checks that a value is a valid decimal. Takes one option, `'precision'`, which is
 *   an optional integer value defining the level of precision the decimal number must match.
 *
 * - `email`: Checks that a value is (probably) a valid email address. The subject of validating
 *   an actual email address is complicated and problematic. A regular expression that correctly
 *   validates addresses against [RFC 5322](http://tools.ietf.org/html/rfc5322) would be several
 *   pages long, with the drawback of being unable to keep up as new top-level domains are added.
 *   Instead, this validator uses PHP's internal input filtering API to check the format, and
 *   provides an option, `'deep'` ( _boolean_) which, if set to `true`, will validate that the email
 *   address' domain contains a valid MX record. Keep in mind, this is just one of the many ways to
 *   validate an email address in the overall context of an application. For other ideas or
 *   examples, [ask Sean](http://seancoates.com/).
 *
 * - `empty`: Checks that a field is left blank **OR** only whitespace characters are present in its
 *   value. Whitespace characters include spaces, tabs, carriage returns and newlines.
 *
 * - `equalTo`: This rule will ensure that the value is equal to another field. The available
 *   options are `'key'` and `'data'`, which designate the matching key and the data array the
 *   value must match on.
 *
 * - `inList`: Checks that a value is in a pre-defined list of values. This validator accepts one
 *   option, `'list'`, which is an array containing acceptable values.
 *
 * - `inRange`: Checks that a numeric value is within a specified range. This value has two options,
 *    `'upper'` and `'lower'`, which specify the boundary of the value.
 *
 * - `integer`: Checks that a value is an integer.
 *
 * - `ip`: Validates a string as a valid IPv4 or IPv6 address.
 *
 * - `length`: Checks that a string length is less than given length. The available option is `'length'`,
 *   which designate the required length of the string.
 *
 * - `lengthBetween`: Checks that a string length is within a specified range. Spaces are included
 *   in the character count. The available options are `'min'` and `'max'`, which designate the
 *   minimum and maximum length of the string.
 *
 * - `lengthMax`: Checks that a string length is less than given length. The available option is `'length'`,
 *   which designate the maximum required length of the string.
 *
 * - `lengthMin`: Checks that a string length is greater than given length. The available option is `'length'`,
 *   which designate the minimum required length of the string.
 *
 * - `luhn`: Checks that a value is a valid credit card number according to the
 *   [Luhn algorithm](http://en.wikipedia.org/wiki/Luhn_algorithm). (See also: the `creditCard`
 *   validator).
 *
 * - `max`: Checks that a value is less than a given maximum. The available options is `'max'`,
 *   which designate the maximum required.
 *
 * - `min`: Checks that a value is greater than a given minimum. The available options is `'min'`,
 *   which designate the minimum required.
 *
 * - `money`: Checks that a value is a valid monetary amount. This rule has two formats, `'right'`
 *   and `'left'`, which indicates which side the monetary symbol (i.e. $) appears on.
 *
 * - `numeric`: Checks that a value is numeric.
 *
 * - `phone`: Check that a value is a valid phone number, non-locale-specific phone number.
 *
 * - `regex`: Checks that a value appears to be a valid regular expression, possibly
 *   containing PCRE-compatible options flags.
 *
 * - `time`: Checks that a value is a valid time. Validates time as 24hr (HH:MM) or am/pm
 *   ([ H]H:MM[a|p]m). Does not allow / validate seconds.
 *
 * - `url`: Checks that a value is a valid URL according to
 *   [RFC 2395](http://www.faqs.org/rfcs/rfc2396.html). Uses PHP's filter API, and accepts any
 *   options accepted for
 *   [the validation URL filter](http://www.php.net/manual/en/filter.filters.validate.php).
 *
 * - `uuid`: Checks that a value is a valid UUID.
 */
class Checker {

    /**
     * Global validation handlers.
     *
     * @var array
     */
    protected static $_handlers = [];

    /**
     * The error messages.
     *
     * @var Closure
     */
    protected static $_messages = [];

    /**
     * Sets or replaces one or several built-in validation rules.
     *
     * For example:
     * {{{
     * Checker::set('zeroToNine', '/^[0-9]$/');
     * $isValid = Checker::isZeroToNine("5"); // true
     * $isValid = Checker::isZeroToNine("20"); // false
     * }}}
     *
     * Alternatively, the first parameter may be an array of rules expressed as key/value pairs,
     * as in the following:
     * {{{
     * Checker::set([
     *  'zeroToNine' => '/^[0-9]$/',
     *  'tenToNineteen' => '/^1[0-9]$/',
     * ]);
     * }}}
     *
     * In addition to regular expressions, validation rules can also be defined as full anonymous
     * functions:
     * {{{
     * use app\models\Account;
     *
     * Checker::set('accountActive', function($value, $options = [], &$params = []) {
     *   $value = is_int($value) ? Account::first($value) : $value;
     *   return (boolean) $value->is_active;
     * });
     *
     * $testAccount = Account::create(['is_active' => false]);
     * Checker::isAccountActive($testAccount); // returns false
     * }}}
     *
     * These functions can take up to 3 parameters:
     *  - `$value`  _mixed_ : This is the actual value to be validated (as in the above example).
     *  - `$options` _array_: This parameter allows a validation rule to implement custom options.
     *                        - `'check'` _string_: Often, validation rules come in multiple "formats", for example:
     *                           credit cards, which vary by type of card. Defining multiple formats allows you to
     *                           retain flexibility in how you validate data. The value of `'check'` can be a specific
     *                           validation handler name or `'any'` which should pass if any validation handler matches.
     *
     * @param mixed  $name The name of the validation rule (string), or an array of key/value pairs
     *                     of names and rules.
     * @param string $rule If $name is a string, this should be a string regular expression, or a
     *                     closure that returns a boolean indicating success. Should be left blank if
     *                     `$name` is an array.
     */
    public static function set($name, $handler = null)
    {
        if (!is_array($name)) {
            $name = [$name => $handler];
        }
        static::$_handlers = static::$_handlers + $name;
    }

    /**
     * Checks if a validation handler exists.
     *
     * @param string $name A validation handler name.
     */
    public static function has($name)
    {
        return isset(static::$_handlers[$name]);
    }

    /**
     * Returns a validation handler.
     *
     * @param string $name A validation handler name.
     */
    public static function get($name)
    {
        if (isset(static::$_handlers[$name])) {
           return static::$_handlers[$name];
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
    public static function handlers($handlers = [], $append = true)
    {
        if (!func_num_args()) {
            return static::$_handlers;
        }
        if ($append) {
            static::$_handlers = array_merge(static::$_handlers, $handlers);
        } else {
            static::$_handlers = $handlers;
        }
        return static::handlers();
    }

    /**
     * Checks a single value against a validation handler.
     *
     * @param  string  $rule    The validation handler name.
     * @param  mixed   $value   The value to check.
     * @param  array   $options The options array.
     * @param  array   $params  A result array with parameters ready to by displayed.
     * @return boolean          Returns `true` or `false` indicating whether the validation rule check
     *                          succeeded or failed.
     */
    public static function is($name, $value, $options = [], &$params = [])
    {
        $not = false;
        if (strncmp($name, 'not:', 4) === 0) {
            $name = substr($name, 4);
            $not = true;
        }

        $handlers = static::get($name);
        $handlers = is_array($handlers) ? $handlers : [$handlers];

        return static::check($value, $handlers, $options, $params) !== $not;
    }

    /**
     * Maps method calls to validation rule names.  For example, a validation rule that would
     * normally be called as `Checker::is('email', 'foo@bar.com')` can also be called as
     * `Checker::isEmail('foo@bar.com')`.
     *
     * @param  string  $method The name of the method called.
     * @param  array   $params The passed parameters
     * @return boolean
     */
    public static function __callStatic($method, $params = [])
    {
        if (!isset($params[0])) {
            return false;
        }
        $name = preg_replace_callback("/^is(Not)?([A-Z][A-Za-z0-9]+)$/", function($matches) {
            $name = lcfirst($matches[2]);
            return $matches[1] ? 'not:' . $name : $name;
        }, $method);
        return static::is($name, $params[0], isset($params[1]) ?  $params[1] : []);
    }

    /**
     * Perform validation checks against a value using an array of all possible formats for a rule,
     * and an array specifying which formats within the rule to use.
     *
     * Checks a single value against a validation handler.
     *
     * @param  mixed   $value    The value to check.
     * @param  array   $handlers An array of validation handler.
     * @param  array   $options  The options array.
     * @param  array   $params   A result array with parameters ready to by displayed.
     * @return boolean           Returns `true` or `false` indicating whether the validation rule check
     *                           succeeded or failed.
     */
    public static function check($value, $handlers, $options, &$params = [])
    {
        $defaults = ['check' => 'any'];
        $options += $defaults;

        $params = [];
        $any = $options['check'] === 'any';
        $formats = (array) $options['check'];

        foreach ($handlers as $index => $check) {
            if (!$any && !in_array($index, $formats)) {
                continue;
            }

            if (is_string($check)) {
                if (preg_match($check, $value)) {
                    return true;
                }
            } elseif (call_user_func_array($check, [$value, $options, &$params])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets/sets a particular error message
     *
     * @param  string $name    A validation handler name.
     * @param  string $message The validation handler message to set or none to get it.
     * @return                 The validation handler message.
     */
    public static function message($name, $message = null)
    {
        if (func_num_args() === 2) {
            return static::$_messages[$name] = $message;
        }
        return isset(static::$_messages[$name]) ? static::$_messages[$name] : static::$_messages['_default_'];
    }

    /**
     * Gets/sets error messages.
     *
     * @param  array $messages The error message array to set or none to get the setted ones.
     * @return array           The error messages.
     */
    public static function messages($messages = [], $append = true)
    {
        if (func_num_args()) {
            static::$_messages = $append ? array_merge(static::$_messages, $messages) : $messages + ['_default_' => 'is invalid'];
        }
        return static::$_messages;
    }

    /**
     * Resets or removes all defined error messages.
     *
     * @param boolean $totaly If `true` error messages will be completly deleted and not reseted.
     */
    public static function reset($totaly = false)
    {
        static::$_handlers = [];
        static::$_messages = ['_default_' => 'is invalid'];

        if ($totaly === true) {
            return;
        }

        static::messages([
            'accepted'      => 'must be accepted',
            'alphaNumeric'  => 'must contain only letters a-z and/or numbers 0-9',
            'boolean'       => 'must be a boolean',
            'creditCard'    => 'must be a valid credit card number',
            'date'          => 'is not a valid date',
            'dateAfter'     => 'must be date after {:date}',
            'dateBefore'    => 'must be date before {:date}',
            'dateFormat'    => 'must be date with format {:format}',
            'decimal'       => 'must be decimal',
            'email'         => 'is not a valid email address',
            'equalTo'       => 'must be the equal to the field `{:key}`',
            'empty'         => 'must be a empty',
            'not:empty'     => 'must not be a empty',
            'inList'        => 'must contain a valid value',
            'not:inList'    => 'must contain a valid value',
            'inRange'       => 'must be inside the range',
            'not:inRange'   => 'must be ouside the range',
            'integer'       => 'must be an integer',
            'ip'            => 'must be an ip',
            'length'        => 'must be longer than {:length}',
            'lengthBetween' => 'must be between {:min} and {:max} characters',
            'lengthMax'     => 'must contain less than {:length} characters',
            'lengthMin'     => 'must contain greater than {:length} characters',
            'luhn'          => 'must be a valid credit card number',
            'max'           => 'must be no more than {:max}',
            'min'           => 'must be at least {:min}',
            'money'         => 'must be a valid monetary amount',
            'numeric'       => 'must be numeric',
            'phone'         => 'must be a phone number',
            'regex'         => 'contains invalid characters',
            'required'      => 'is required',
            'time'          => 'must be a valid time',
            'url'           => 'not a URL'
        ]);

        static::set([
            'accepted' => function($value, $options = [], &$params = []) {
                $bool = is_bool($value);
                $filter = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                return ($bool || $filter !== null && $value !== null);
            },
            'alphaNumeric' => function($value, $options = [], &$params = []) {
                $rules = ['/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu'];
                return (empty($value) && $value !== '0') ? false : static::check($value, $rules, $options);
            },
            'boolean' => function($value, $options = [], &$params = []) {
                return in_array($value, [0, 1, '0', '1', true, false], true);
            },
            'creditCard' => function($value, $options = [], &$params = []) {
                $rules = [
                    'amex'     => '/^3[4|7]\\d{13}$/',
                    'bankcard' => '/^56(10\\d\\d|022[1-5])\\d{10}$/',
                    'diners'   => '/^(?:3(0[0-5]|[68]\\d)\\d{11})|(?:5[1-5]\\d{14})$/',
                    'disc'     => '/^(?:6011|650\\d)\\d{12}$/',
                    'electron' => '/^(?:417500|4917\\d{2}|4913\\d{2})\\d{10}$/',
                    'enroute'  => '/^2(?:014|149)\\d{11}$/',
                    'jcb'      => '/^(3\\d{4}|2100|1800)\\d{11}$/',
                    'maestro'  => '/^(?:5020|6\\d{3})\\d{12}$/',
                    'mc'       => '/^5[1-5]\\d{14}$/',
                    'solo'     => '/^(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?$/',
                    'switch'   => '/^(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})' .
                                  '\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4]' .
                                  '[0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)$/',
                    'visa'     => '/^4\\d{12}(\\d{3})?$/',
                    'voyager'  => '/^8699[0-9]{11}$/',
                    'fast'     => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3' .
                                  '(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/'
                ];
                $options += ['deep' => false];

                if (strlen($value = str_replace(['-', ' '], '', $value)) < 13) {
                    return false;
                }
                if (!static::check($value, $rules, $options)) {
                    return false;
                }
                return $options['deep'] ? static::isLuhn($value) : true;
            },
            'date' => function($value, $options = [], &$params = []) {
                if (is_string($value)) {
                    return strtotime($value) !== false;
                }
                return $value instanceof DateTime;
            },
            'dateAfter' => function($value, $options = [], &$params = []) {
                if (!isset($options['date'])) {
                    return false;
                }
                $after = $options['date'];
                $vtime = $value instanceof DateTime ? $value->getTimestamp() : strtotime($value);
                $atime = $after instanceof DateTime ? $after->getTimestamp() : strtotime($after);
                $params['date'] = date('Y-m-d H:i:s', $atime);
                return $vtime >= $atime;
            },
            'dateBefore' => function($value, $options = [], &$params = []) {
                if (!isset($options['date'])) {
                    return false;
                }
                $before = $options['date'];
                $vtime = $value instanceof DateTime ? $value->getTimestamp() : strtotime($value);
                $btime = $before instanceof DateTime ? $before->getTimestamp() : strtotime($before);
                $params['date'] = date('Y-m-d H:i:s', $btime);
                return $vtime <= $btime;
            },
            'dateFormat' => function($value, $options = [], &$params = []) {
                $defaults = ['format' => 'Y-m-d H:i:s'];
                $options += $defaults;
                $parsed = date_parse_from_format($options['format'], $value);
                return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0 && $parsed['month'] <=12;
            },
            'decimal' => function($value, $options = [], &$params = []) {
                if (isset($options['precision'])) {
                    $precision = strlen($value) - strrpos($value, '.') - 1;

                    if ($precision !== (int) $options['precision']) {
                        return false;
                    }
                }
                return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) !== null;
            },
            'email' => function($value, $options = [], &$params = []) {
                $defaults = ['deep' => false];
                $options += $defaults;

                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
                if (!$options['deep']) {
                    return true;
                }
                list($prefix, $host) = explode('@', $value);

                $mxhosts = [];
                if (getmxrr($host, $mxhosts)) {
                    return is_array($mxhosts);
                }
                return false;
            },
            'empty'  => '/^\\s*$/',
            'equalTo' => function($value, $options = [], &$params = []) {
                if (!isset($options['key'])) {
                    return false;
                }
                $field = $options['key'];
                return isset($options['data'][$field]) && $value == $options['data'][$field];
            },
            'inList' => function($value, $options) {
                $options += ['list' => []];
                $strict = is_bool($value) || $value === '' || $value === null;
                return in_array($value, $options['list'], $strict);
            },
            'inRange' => function($value, $options = [], &$params = []) {
                $defaults = ['upper' => null, 'lower' => null];
                $options += $defaults;

                if (!is_numeric($value)) {
                    return false;
                }
                switch (true) {
                    case ($options['upper'] !== null && $options['lower'] !== null):
                        return ($value >= $options['lower'] && $value <= $options['upper']);
                    case ($options['upper'] !== null):
                        return ($value <= $options['upper']);
                    case ($options['lower'] !== null):
                        return ($value >= $options['lower']);
                }
                return is_finite($value);
            },
            'integer' => function($value, $options = [], &$params = []) {
                $options += ['flags' => []];
                return filter_var($value, FILTER_VALIDATE_INT, $options);
            },
            'ip' => function($value, $options = [], &$params = []) {
                $options += ['flags' => []];
                return (boolean) filter_var($value, FILTER_VALIDATE_IP, $options);
            },
            'length' => function($value, $options = [], &$params = []) {
                return isset($options['length']) && strlen($value) === $options['length'];
            },
            'lengthBetween' => function($value, $options = [], &$params = []) {
                $length = strlen($value);
                return isset($options['min']) && isset($options['max']) && $length >= $options['min'] && $length <= $options['max'];
            },
            'lengthMax' => function($value, $options = [], &$params = []) {
                $length = strlen($value);
                return isset($options['length']) && $length <= $options['length'];
            },
            'lengthMin' => function($value, $options = [], &$params = []) {
                $length = strlen($value);
                return isset($options['length']) && $length >= $options['length'];
            },
            'luhn' => function($value, $options = [], &$params = []) {
                if (empty($value) || !is_string($value)) {
                    return false;
                }
                $sum = 0;
                $length = strlen($value);

                for ($position = 1 - ($length % 2); $position < $length; $position += 2) {
                    $sum += $value[$position];
                }
                for ($position = ($length % 2); $position < $length; $position += 2) {
                    $number = $value[$position] * 2;
                    $sum += ($number < 10) ? $number : $number - 9;
                }
                return ($sum % 10 === 0);
            },
            'max' => function($value, $options = [], &$params = []) {
                return isset($options['max']) && $value <= $options['max'];
            },
            'min' => function($value, $options = [], &$params = []) {
                return isset($options['min']) && $value >= $options['min'];
            },
            'money' => [
                'right'    => '/^(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))' .
                              '((?!\1)[,.]\d{2})?(?<!\x{00a2})\p{Sc}?$/u',
                'left'     => '/^(?!\x{00a2})\p{Sc}?(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?' .
                              '(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?$/u'
            ],
            'numeric' => function($value, $options = [], &$params = []) {
                return is_numeric($value);
            },
            'phone' => '/^\+?[0-9\(\)\-]{10,20}$/',
            'regex' => '/^(?:([^[:alpha:]\\\\{<\[\(])(.+)(?:\1))|(?:{(.+)})|(?:<(.+)>)|' .
                       '(?:\[(.+)\])|(?:\((.+)\))[gimsxu]*$/',
            'time' => '%^((0?[1-9]|1[012])(:[0-5]\d){0,2}([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$%',
            'url' => function($value, $options = [], &$params = []) {
                $options += ['flags' => []];
                return (boolean) filter_var($value, FILTER_VALIDATE_URL, $options);
            },
            'uuid' => "/^[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}$/"
        ]);
    }
}

Checker::reset();
