<?php
namespace Lead\Validator\Spec\Suite;

use InvalidArgumentException;
use DateTime;
use Lead\Validator\Validator;
use Lead\Validator\Checker;

use Kahlan\Plugin\Double;

describe("Validator", function() {

    afterEach(function() {
        Checker::reset();
    });

    describe("->__construct()", function() {

        it("correctly sets local validation handlers", function() {

            $validator = new Validator([
                'handlers' => [
                    'zeroToNine' => '/^[0-9]$/',
                    'tenToNineteen' => '/^1[0-9]$/'
                ]
            ]);

            expect($validator->handlers())->toContainKeys('zeroToNine', 'tenToNineteen');

        });

    });

    describe("->meta()", function() {

        it("gets/sets meta data", function() {

            $validator = new Validator();

            $meta = ['model' => 'Post'];
            expect($validator->meta($meta))->toBe($meta);
            expect($validator->meta())->toBe($meta);

        });

    });

    describe("->error()", function() {

        it("gets/sets the error handler", function() {

            $validator = new Validator();

            $handler = function() { return 'hello world'; };
            expect($validator->error($handler))->toBe($handler);
            expect($validator->error())->toBe($handler);

        });

    });

    describe("->get()", function() {

        it("throws an exceptions for unexisting validation handler", function() {

            $closure = function() {
                $validator = new Validator();
                $validator->get('abc');
            };

            expect($closure)->toThrow(new InvalidArgumentException("Unexisting `abc` as validation handler."));

        });

    });


    describe("->set()", function() {

        it("sets some local handlers", function() {

            $validator = new Validator();
            $validator->set('zeroToNine', '/^[0-9]$/');
            $validator->set('tenToNineteen', '/^1[0-9]$/');

            expect($validator->has('zeroToNine'))->toBe(true);
            expect($validator->has('tenToNineteen'))->toBe(true);

            expect($validator->get('zeroToNine'))->toBe('/^[0-9]$/');
            expect($validator->get('tenToNineteen'))->toBe('/^1[0-9]$/');

        });

        it("overrides handlers", function() {

            $validator = new Validator();
            $validator->set('zeroToNine', '/^[0-5]$/');
            $validator->set('zeroToNine', '/^[0-9]$/');

            expect($validator->get('zeroToNine'))->toBe('/^[0-9]$/');

        });

    });

    describe("->handlers()", function() {

        beforeEach(function() {

            Checker::reset(true);
            $this->validator = new Validator();
            $this->validator->set('zeroToNine', '/^[0-9]$/');

        });

        it("gets some handlers", function() {

            expect($this->validator->handlers())->toBe(['zeroToNine' => '/^[0-9]$/']);

        });

        it("appends some handlers", function() {

            $expected = ['zeroToNine' => '/^[0-9]$/', 'tenToNineteen' => '/^1[0-9]$/'];
            expect($this->validator->handlers(['tenToNineteen' => '/^1[0-9]$/']))->toBe($expected);
            expect($this->validator->handlers())->toBe($expected);

        });

        it("sets some handlers", function() {

            $expected = ['tenToNineteen' => '/^1[0-9]$/'];
            expect($this->validator->handlers(['tenToNineteen' => '/^1[0-9]$/'], false))->toBe($expected);
            expect($this->validator->handlers())->toBe($expected);

        });

    });

    describe("->is()", function() {

        beforeEach(function() {
            $this->checker = Double::classname(['extends' => Checker::class]);
            $this->validator = new Validator([
                'classes' => [
                    'checker' => $this->checker
                ]
            ]);
        });

        it("delegates to the checker", function() {

            $checker = $this->checker;
            $handler = $checker::get('alphaNumeric');
            expect($checker)->toReceive('::check')->with('frferrf', [$handler] , [
                'hello' => 'world'
            ]);

            $this->validator->is('alphaNumeric', 'frferrf', ['hello' => 'world']);

        });
    });

    describe("->__call()", function() {

        beforeEach(function() {
            $this->checker = Double::classname(['extends' => Checker::class]);
            $this->validator = new Validator([
                'classes' => [
                    'checker' => $this->checker
                ]
            ]);
        });

        it("delegates to the checker", function() {

            $checker = $this->checker;
            $handler = $checker::get('alphaNumeric');
            expect($checker)->toReceive('::check')->with('frferrf', [$handler] , [
                'hello' => 'world'
            ]);

            $this->validator->isAlphaNumeric('frferrf', ['hello' => 'world']);

        });

        it("bails out with no passed parameters", function() {

            expect($this->validator->isAlphaNumeric())->toBe(false);

        });
    });

    describe("->validates()", function() {

        beforeEach(function() {
            $this->validator = new Validator();
        });

        it("fails for rules with missing data", function() {

            $this->validator->rule('title', 'not:empty');

            expect($this->validator->validates([]))->toBe(false);
            expect($this->validator->errors())->toBe(['title' => ['is required']]);

            expect($this->validator->validates(['title' => '']))->toBe(false);
            expect($this->validator->errors())->toBe(['title' => ['must not be a empty']]);

        });

        it("fails for rules with missing data and uses a custom message", function() {

            $this->validator->rule('title', [
                'not:empty' => ['message' => 'please enter a title']
            ]);

            expect($this->validator->validates([]))->toBe(false);
            expect($this->validator->errors())->toBe(['title' => ['is required']]);

            expect($this->validator->validates(['title' => '']))->toBe(false);
            expect($this->validator->errors())->toBe(['title' => ['please enter a title']]);

        });

        it("allows short syntax", function() {

            $this->validator->rule('title', ['not:empty' => 'please enter a title']);

            expect($this->validator->validates(['title' => '']))->toBe(false);
            expect($this->validator->errors())->toBe(['title' => ['please enter a title']]);

        });

        it("checks all rules", function() {

            $this->validator->rule('title', [
                'not:empty'     => ['message' => 'please enter a {:field}'],
                'lengthBetween' => ['min' => 1, 'max' => 7, 'message' => 'must be between {:min} and {:max} character long']
            ]);

            expect($this->validator->validates([]))->toBe(false);
            expect($this->validator->errors())->toBe(['title' => ['is required']]);

            expect($this->validator->validates(['title' => '']))->toBe(false);
            expect($this->validator->errors())->toBe(['title' => [
                'please enter a title',
                'must be between 1 and 7 character long'
            ]]);

        });

        it("passes for rules with missing data but not required", function() {

            $this->validator->rule('title', [
                'not:empty'     => [
                    'message'  => 'please enter a {:field}',
                    'required' => false
                ]
            ]);

            expect($this->validator->validates([]))->toBe(true);
            expect($this->validator->errors())->toBe([]);

        });

        it("passes for rules with empty data but allowed by skipEmpty", function() {

            $this->validator->rule('title', [
                'not:empty'     => [
                    'message'   => 'please enter a {:field}',
                    'skipEmpty' => true
                ]
            ]);

            expect($this->validator->validates(['title' => '']))->toBe(true);
            expect($this->validator->errors())->toBe([]);

        });

        it("passes if valid", function() {

            $this->validator->rule('title', 'not:empty');
            expect($this->validator->validates(['title' => 'new title']))->toBe(true);

            expect($this->validator->errors())->toBe([]);

        });

        it("checks rules which fit the event", function() {

            $this->validator->rule('title', [
                'not:empty' => [
                    'message' => 'please enter a {:field}',
                    'on'      => 'create'
                ]
            ]);

            expect($this->validator->validates(['title' => ''], ['events' => 'create']))->toBe(false);
            expect($this->validator->errors())->toBe(['title' => ['please enter a title']]);

        });

        it("ignores rules which doesn't fit the event", function() {

            $this->validator->rule('title', [
                'not:empty' => [
                    'message' => 'please enter a {:field}',
                    'on'      => 'create'
                ]
            ]);

            expect($this->validator->validates(['title' => ''], ['events' => 'update']))->toBe(true);
            expect($this->validator->errors())->toBe([]);

        });

        it("validates arrays of things", function() {

            $this->validator->rule('emails.*', 'email');

            expect($this->validator->validates(['emails' => ['willy@boy.com', 'johnny@boy.com']]))->toBe(true);
            expect($this->validator->errors())->toBe([]);

        });

        it("provides errors reporting for arrays of things", function() {

            $this->validator->rule('emails.*', 'email');

            expect($this->validator->validates(['emails' => ['invalid', 'johnny@boy.com']]))->toBe(false);
            expect($this->validator->errors())->toBe(['emails.0' => ['is not a valid email address']]);

            expect($this->validator->validates(['emails' => ['willy@boy.com', 'invalid']]))->toBe(false);
            expect($this->validator->errors())->toBe(['emails.1' => ['is not a valid email address']]);

        });

        it("validates nested structure", function() {

            $this->validator->rule('people.*.email', 'email');

            expect($this->validator->validates([
                'people' => [
                    ['email' => 'willy@boy.com'],
                    ['email' => 'johnny@boy.com']
                ]
            ]))->toBe(true);

            expect($this->validator->errors())->toBe([]);

        });

        it("provides errors reporting for nested structure", function() {

            $this->validator->rule('people.*.email', 'email');

            expect($this->validator->validates([
                'people' => [
                    ['email' => 'invalid'],
                    ['email' => 'johnny@boy.com']
                ]
            ]))->toBe(false);

            expect($this->validator->errors())->toBe(['people.0.email' => ['is not a valid email address']]);

            expect($this->validator->validates([
                'people' => [
                    ['email' => 'willy@boy.com'],
                    ['email' => 'invalid']
                ]
            ]))->toBe(false);

            expect($this->validator->errors())->toBe(['people.1.email' => ['is not a valid email address']]);
        });

    });

    describe("->message()", function() {

        beforeEach(function() {
            $this->validator = new Validator();
        });

        it("checks defaults error message", function() {

            $this->validator->rule('accepted', 'accepted');
            $this->validator->rule('alphaNumeric', 'alphaNumeric');
            $this->validator->rule('boolean', 'boolean');
            $this->validator->rule('creditCard', 'creditCard');
            $this->validator->rule('date', 'date');
            $this->validator->rule('dateAfter', [
                'dateAfter' => [
                    'date' => DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-31 11:59:59')
                ]
            ]);
            $this->validator->rule('dateBefore', [
                'dateBefore' => [
                    'date' => DateTime::createFromFormat('Y-m-d H:i:s', '2014-12-31 11:59:59')
                ]
            ]);
            $this->validator->rule('dateFormat', ['dateFormat' => ['format' => 'Y-m-d H:i:s']]);
            $this->validator->rule('decimal', 'decimal');
            $this->validator->rule('email', 'email');
            $this->validator->rule('equalTo', ['equalTo' => ['key' => 'fieldname']]);
            $this->validator->rule('empty', 'empty');
            $this->validator->rule('not:empty', 'not:empty');
            $this->validator->rule('inList', 'inList');
            $this->validator->rule('not:inList', 'not:inList');
            $this->validator->rule('inRange', 'inRange');
            $this->validator->rule('not:inRange', 'not:inRange');
            $this->validator->rule('integer', 'integer');
            $this->validator->rule('ip', 'ip');
            $this->validator->rule('length', ['length' => ['length' => 5]]);
            $this->validator->rule('lengthBetween', ['lengthBetween' => ['min' => 5, 'max' => 15]]);
            $this->validator->rule('lengthMax', ['lengthMax' => ['length' => 5]]);
            $this->validator->rule('lengthMin', ['lengthMin' => ['length' => 5]]);
            $this->validator->rule('luhn', 'luhn');
            $this->validator->rule('max', ['max' => ['max' => 5]]);
            $this->validator->rule('min', ['min' => ['min' => 5]]);
            $this->validator->rule('money', 'money');
            $this->validator->rule('numeric', 'numeric');
            $this->validator->rule('phone', 'phone');
            $this->validator->rule('regex', 'regex');
            $this->validator->rule('required', 'not:empty');
            $this->validator->rule('time', 'time');
            $this->validator->rule('undefined', 'undefined');
            $this->validator->rule('url', 'url');

            $this->validator->set('undefined', function() {
                return false;
            });

            expect($this->validator->validates([
                'accepted'      => '',
                'alphaNumeric'  => '',
                'boolean'       => '',
                'creditCard'    => '',
                'date'          => '',
                'dateAfter'     => '2014-12-31 11:59:59',
                'dateBefore'    => '2015-12-31 11:59:59',
                'dateFormat'    => '',
                'decimal'       => '',
                'email'         => '',
                'equalTo'       => '',
                'empty'         => 'not empty',
                'not:empty'     => '',
                'inList'        => '',
                'not:inList'    => '',
                'inRange'       => '',
                'not:inRange'   => '',
                'integer'       => '',
                'ip'            => '',
                'length'        => '',
                'lengthBetween' => '',
                'lengthMax'     => '',
                'lengthMin'     => '',
                'luhn'          => '',
                'max'           => '15',
                'min'           => '',
                'money'         => '',
                'numeric'       => '',
                'phone'         => '',
                'regex'         => '',
                'time'          => '',
                'undefined'     => '',
                'url'           => ''
            ]))->toBe(false);

            expect($this->validator->errors())->toBe([
                'alphaNumeric'  => ['must contain only letters a-z and/or numbers 0-9'],
                'boolean'       => ['must be a boolean'],
                'creditCard'    => ['must be a valid credit card number'],
                'date'          => ['is not a valid date'],
                'dateAfter'     => ['must be date after 2015-12-31 11:59:59'],
                'dateBefore'    => ['must be date before 2014-12-31 11:59:59'],
                'dateFormat'    => ['must be date with format Y-m-d H:i:s'],
                'decimal'       => ['must be decimal'],
                'email'         => ['is not a valid email address'],
                'equalTo'       => ['must be the equal to the field `fieldname`'],
                'empty'         => ['must be a empty'],
                'not:empty'     => ['must not be a empty'],
                'inList'        => ['must contain a valid value'],
                'inRange'       => ['must be inside the range'],
                'integer'       => ['must be an integer'],
                'ip'            => ['must be an ip'],
                'length'        => ['must be longer than 5'],
                'lengthBetween' => ['must be between 5 and 15 characters'],
                'lengthMin'     => ['must contain greater than 5 characters'],
                'luhn'          => ['must be a valid credit card number'],
                'max'           => ['must be no more than 5'],
                'min'           => ['must be at least 5'],
                'money'         => ['must be a valid monetary amount'],
                'numeric'       => ['must be numeric'],
                'phone'         => ['must be a phone number'],
                'regex'         => ['contains invalid characters'],
                'required'      => ['is required'],
                'time'          => ['must be a valid time'],
                'undefined'     => ['is invalid'],
                'url'           => ['not a URL']
            ]);

        });

        it("gets a error message", function() {

            expect($this->validator->message('required'))->toBe('is required');

        });

        it("sets an error message", function() {

            expect($this->validator->message('required', 'must be defined'))->toBe('must be defined');
            expect($this->validator->message('required'))->toBe('must be defined');

        });

    });

    describe("->messages()", function() {

        it("appends error messages", function() {

            Checker::reset(true);
            $validator = new Validator();
            $validator->messages(['a' => 'b']);

            $expected = ['a' => 'b', 'c' => 'd', '_default_' => 'is invalid'];
            expect($validator->messages(['c' => 'd']))->toBe($expected);
            expect($validator->messages())->toBe($expected);

        });

        it("sets error messages", function() {

            Checker::reset(true);
            $validator = new Validator();
            $validator->messages(['a' => 'b']);

            $expected = ['c' => 'd', '_default_' => 'is invalid'];
            expect($validator->messages(['c' => 'd'], false))->toBe($expected);
            expect($validator->messages())->toBe($expected);

        });

    });

    describe("::values()", function() {

        it("returns the wrapped data when no path is defined", function() {

            $data = ['title' => 'new title'];

            expect(Validator::values($data))->toBe([$data]);

        });

    });

});
