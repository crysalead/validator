<?php
namespace validator\spec\suite;

use InvalidArgumentException;
use stdClass;
use DateTime;
use validator\Checker;

use kahlan\plugin\Monkey;

describe("Checker", function() {

    afterEach(function() {
        Checker::reset();
    });

    describe("::set()", function() {

        it("adds some local handlers", function() {

            Checker::set('zeroToNine', '/^[0-9]$/');
            Checker::set('tenToNineteen', '/^1[0-9]$/');

            expect(Checker::handlers())->toContainKeys('zeroToNine', 'tenToNineteen');

        });

    });

    describe("::get()", function() {

        it("throws an exceptions for unexisting validation handler", function() {

            $closure = function() {
                Checker::get('abc');
            };

            expect($closure)->toThrow(new InvalidArgumentException("Unexisting `abc` as validation handler."));

        });

    });

    describe("::set()", function() {

        it("sets validation handlers", function() {

            Checker::set('zeroToNine', '/^[0-9]$/');
            Checker::set('tenToNineteen', '/^1[0-9]$/');

            expect(Checker::has('zeroToNine'))->toBe(true);
            expect(Checker::has('tenToNineteen'))->toBe(true);

            expect(Checker::get('zeroToNine'))->toBe('/^[0-9]$/');
            expect(Checker::get('tenToNineteen'))->toBe('/^1[0-9]$/');

        });

    });

    describe("::handlers()", function() {

        beforeEach(function() {

            Checker::reset(true);
            Checker::set('zeroToNine', '/^[0-9]$/');

        });

        it("gets some handlers", function() {

            expect(Checker::handlers())->toBe(['zeroToNine' => '/^[0-9]$/']);

        });

        it("appends some handlers", function() {

            $expected = ['zeroToNine' => '/^[0-9]$/', 'tenToNineteen' => '/^1[0-9]$/'];
            expect(Checker::handlers(['tenToNineteen' => '/^1[0-9]$/']))->toBe($expected);
            expect(Checker::handlers())->toBe($expected);

        });

        it("sets some handlers", function() {

            $expected = ['tenToNineteen' => '/^1[0-9]$/'];
            expect(Checker::handlers(['tenToNineteen' => '/^1[0-9]$/'], false))->toBe($expected);
            expect(Checker::handlers())->toBe($expected);

        });

    });

    describe("::is()", function() {

        it("checks accepted values", function() {

            expect(Checker::is('accepted', true))->toBe(true);
            expect(Checker::is('accepted', false))->toBe(true);
            expect(Checker::is('accepted', 'true'))->toBe(true);
            expect(Checker::is('accepted', 'false'))->toBe(true);
            expect(Checker::is('accepted', 0))->toBe(true);
            expect(Checker::is('accepted', 1))->toBe(true);
            expect(Checker::is('accepted', '0'))->toBe(true);
            expect(Checker::is('accepted', '1'))->toBe(true);
            expect(Checker::is('accepted', 'on'))->toBe(true);
            expect(Checker::is('accepted', 'off'))->toBe(true);
            expect(Checker::is('accepted', 'yes'))->toBe(true);
            expect(Checker::is('accepted', 'no'))->toBe(true);
            expect(Checker::is('accepted', ''))->toBe(true);

            expect(Checker::is('accepted', '11'))->toBe(false);
            expect(Checker::is('accepted', '-1'))->toBe(false);
            expect(Checker::is('accepted', 11))->toBe(false);
            expect(Checker::is('accepted', -1))->toBe(false);
            expect(Checker::is('accepted', 'test'))->toBe(false);
            expect(Checker::is('accepted', null))->toBe(false);

        });

        it("checks alpha numeric values", function() {

            expect(Checker::is('alphaNumeric', 'frferrf'))->toBe(true);
            expect(Checker::is('alphaNumeric', '12234'))->toBe(true);
            expect(Checker::is('alphaNumeric', '1w2e2r3t4y'))->toBe(true);
            expect(Checker::is('alphaNumeric', '0'))->toBe(true);
            expect(Checker::is('alphaNumeric', 'abçďĕʑʘπй'))->toBe(true);
            expect(Checker::is('alphaNumeric', 'ˇˆๆゞ'))->toBe(true);
            expect(Checker::is('alphaNumeric', 'אกあアꀀ豈'))->toBe(true);
            expect(Checker::is('alphaNumeric', 'ǅᾈᾨ'))->toBe(true);
            expect(Checker::is('alphaNumeric', 'ÆΔΩЖÇ'))->toBe(true);
            expect(Checker::is('alphaNumeric', '日本語でも'))->toBe(true);
            expect(Checker::is('alphaNumeric', 'をありがとうございました'))->toBe(true);

            expect(Checker::is('alphaNumeric', '12 234'))->toBe(false);
            expect(Checker::is('alphaNumeric', 'dfd 234'))->toBe(false);
            expect(Checker::is('alphaNumeric', 'こんにちは！'))->toBe(false);
            expect(Checker::is('alphaNumeric', "\n"))->toBe(false);
            expect(Checker::is('alphaNumeric', "\t"))->toBe(false);
            expect(Checker::is('alphaNumeric', "\r"))->toBe(false);
            expect(Checker::is('alphaNumeric', ' '))->toBe(false);
            expect(Checker::is('alphaNumeric', ''))->toBe(false);

        });

        it("checks empty values", function() {

            expect(Checker::is('empty', ''))->toBe(true);
            expect(Checker::is('empty', '  '))->toBe(true);
            expect(Checker::is('empty', "\n\t"))->toBe(true);

            expect(Checker::is('empty', '12234'))->toBe(false);
            expect(Checker::is('empty', 'dfdQSD'))->toBe(false);
            expect(Checker::is('empty', 'こんにちは！'))->toBe(false);

        });

        it("checks accepted values", function() {

            expect(Checker::is('boolean', true))->toBe(true);
            expect(Checker::is('boolean', false))->toBe(true);
            expect(Checker::is('boolean', 0))->toBe(true);
            expect(Checker::is('boolean', 1))->toBe(true);
            expect(Checker::is('boolean', '0'))->toBe(true);
            expect(Checker::is('boolean', '1'))->toBe(true);

            expect(Checker::is('boolean', 'true'))->toBe(false);
            expect(Checker::is('boolean', 'false'))->toBe(false);
            expect(Checker::is('boolean', 'on'))->toBe(false);
            expect(Checker::is('boolean', 'off'))->toBe(false);
            expect(Checker::is('boolean', 'yes'))->toBe(false);
            expect(Checker::is('boolean', 'no'))->toBe(false);
            expect(Checker::is('boolean', ''))->toBe(false);
            expect(Checker::is('boolean', '11'))->toBe(false);
            expect(Checker::is('boolean', '-1'))->toBe(false);
            expect(Checker::is('boolean', 11))->toBe(false);
            expect(Checker::is('boolean', -1))->toBe(false);
            expect(Checker::is('boolean', 'test'))->toBe(false);
            expect(Checker::is('boolean', null))->toBe(false);

        });

        it("checks credit card values", function() {

            /* American Express */
            expect(Checker::is('creditCard', '370482756063980', ['check' => 'amex']))->toBe(true);
            expect(Checker::is('creditCard', '3491-0643-3773-483', ['check' => 'amex']))->toBe(true);
            expect(Checker::is('creditCard', '344671486204764', ['check' => 'amex']))->toBe(true);
            expect(Checker::is('creditCard', '341779292230411', ['check' => 'amex']))->toBe(true);
            expect(Checker::is('creditCard', '341646919853372', ['check' => 'amex']))->toBe(true);
            expect(Checker::is('creditCard', '348498616319346', ['check' => 'amex', 'deep' => true]))->toBe(true);
            expect(Checker::is('creditCard', '5610376649499352', ['check' => 'amex']))->toBe(false);

            /* BankCard */
            expect(Checker::is('creditCard', '5610 7458 6741 3420', ['check' => 'bankcard']))->toBe(true);
            expect(Checker::is('creditCard', '5610376649499352', ['check' => 'bankcard']))->toBe(true);
            expect(Checker::is('creditCard', '5610091936000694', ['check' => 'bankcard']))->toBe(true);
            expect(Checker::is('creditCard', '5610139705753702', ['check' => 'bankcard']))->toBe(true);
            expect(Checker::is('creditCard', '5602226032150551', ['check' => 'bankcard']))->toBe(true);
            expect(Checker::is('creditCard', '5602223993735777', ['check' => 'bankcard']))->toBe(true);
            expect(Checker::is('creditCard', '30155483651028', ['check' => 'bankcard']))->toBe(false);

            /* Diners Club 14 */
            expect(Checker::is('creditCard', '30155483651028', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '36371312803821', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '38801277489875', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '30348560464296', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '38053196067461', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '36520379984870', ['check' => 'diners']))->toBe(true);

            /* 2004 MasterCard/Diners Club Alliance International 14 */
            expect(Checker::is('creditCard', '36747701998969', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '36427861123159', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '36150537602386', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '36582388820610', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '36729045250216', ['check' => 'diners']))->toBe(true);

            /* 2004 MasterCard/Diners Club Alliance US & Canada 16 */
            expect(Checker::is('creditCard', '5597511346169950', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '5526443162217562', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '5577265786122391', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '5534061404676989', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '5545313588374502', ['check' => 'diners']))->toBe(true);
            expect(Checker::is('creditCard', '6011802876467237', ['check' => 'diners']))->toBe(false);

            /* Discover */
            expect(Checker::is('creditCard', '6011802876467237', ['check' => 'disc']))->toBe(true);
            expect(Checker::is('creditCard', '6506432777720955', ['check' => 'disc']))->toBe(true);
            expect(Checker::is('creditCard', '6011126265283942', ['check' => 'disc']))->toBe(true);
            expect(Checker::is('creditCard', '6500976374623323', ['check' => 'disc']))->toBe(true);
            expect(Checker::is('creditCard', '201496944158937', ['check' => 'disc']))->toBe(false);

            /* enRoute */
            expect(Checker::is('creditCard', '201496944158937', ['check' => 'enroute']))->toBe(true);
            expect(Checker::is('creditCard', '214945833739665', ['check' => 'enroute']))->toBe(true);
            expect(Checker::is('creditCard', '214982692491187', ['check' => 'enroute']))->toBe(true);
            expect(Checker::is('creditCard', '214981579370225', ['check' => 'enroute']))->toBe(true);
            expect(Checker::is('creditCard', '201447595859877', ['check' => 'enroute']))->toBe(true);
            expect(Checker::is('creditCard', '210034762247893', ['check' => 'enroute']))->toBe(false);

            /* JCB 15 digit */
            expect(Checker::is('creditCard', '210034762247893', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '180078671678892', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '210057919192738', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '180031358949367', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '180033802147846', ['check' => 'jcb']))->toBe(true);

            /* JCB 16 digit */
            expect(Checker::is('creditCard', '3096806857839939', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '3158699503187091', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '3112549607186579', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '3528274546125962', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '3528890967705733', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '3337198811307545', ['check' => 'jcb']))->toBe(true);
            expect(Checker::is('creditCard', '5020147409985219', ['check' => 'jcb']))->toBe(false);

            /* Maestro (debit card) */
            expect(Checker::is('creditCard', '5020147409985219', ['check' => 'maestro']))->toBe(true);
            expect(Checker::is('creditCard', '5020931809905616', ['check' => 'maestro']))->toBe(true);
            expect(Checker::is('creditCard', '6339931536544062', ['check' => 'maestro']))->toBe(true);
            expect(Checker::is('creditCard', '6465028615704406', ['check' => 'maestro']))->toBe(true);
            expect(Checker::is('creditCard', '5580424361774366', ['check' => 'maestro']))->toBe(false);

            /* MasterCard */
            expect(Checker::is('creditCard', '5580424361774366', ['check' => 'mc']))->toBe(true);
            expect(Checker::is('creditCard', '5589563059318282', ['check' => 'mc']))->toBe(true);
            expect(Checker::is('creditCard', '5387558333690047', ['check' => 'mc']))->toBe(true);
            expect(Checker::is('creditCard', '5163919215247175', ['check' => 'mc']))->toBe(true);
            expect(Checker::is('creditCard', '5467639122779531', ['check' => 'mc']))->toBe(true);
            expect(Checker::is('creditCard', '5297350261550024', ['check' => 'mc']))->toBe(true);
            expect(Checker::is('creditCard', '5162739131368058', ['check' => 'mc']))->toBe(true);
            expect(Checker::is('creditCard', '6767432107064987', ['check' => 'mc']))->toBe(false);

            /* Solo 16 */
            expect(Checker::is('creditCard', '6767432107064987', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6334667758225411', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6767037421954068', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6767823306394854', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6767493947881311', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6767194235798817', ['check' => 'solo']))->toBe(true);

            /* Solo 18 */
            expect(Checker::is('creditCard', '676714834398858593', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '676751666435130857', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '676781908573924236', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '633487484858610484', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '633453764680740694', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '676768613295414451', ['check' => 'solo']))->toBe(true);

            /* Solo 19 */
            expect(Checker::is('creditCard', '6767838565218340113', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6767760119829705181', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6767265917091593668', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6334647959628261714', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '6334527312384101382', ['check' => 'solo']))->toBe(true);
            expect(Checker::is('creditCard', '5641829171515733', ['check' => 'solo']))->toBe(false);

            /* Switch 16 */
            expect(Checker::is('creditCard', '5641829171515733', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '5641824852820809', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '6759129648956909', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '4936119165483420', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '4936190990500993', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '6333372765092554', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '5641821330950570', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '6759841558826118', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '4936164540922452', ['check' => 'switch']))->toBe(true);

            /* Switch 18 */
            expect(Checker::is('creditCard', '493622764224625174', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '564182823396913535', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '675917308304801234', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '675919890024220298', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '633308376862556751', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '633334008833727504', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '493631941273687169', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '564182971729706785', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '633303461188963496', ['check' => 'switch']))->toBe(true);

            /* Switch 19 */
            expect(Checker::is('creditCard', '6759603460617628716', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '4936705825268647681', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '5641829846600479183', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '4936321036970553134', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '4936111816358702773', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '4936196077254804290', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '6759558831206830183', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '5641827998830403137', ['check' => 'switch']))->toBe(true);
            expect(Checker::is('creditCard', '4024007174754', ['check' => 'switch']))->toBe(false);

            /* Visa 13 digit */
            expect(Checker::is('creditCard', '4024007174754', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4104816460717', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4716229700437', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4539305400213', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4485906062491', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4539365115149', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4485146516702', ['check' => 'visa']))->toBe(true);

            /* Visa 16 digit */
            expect(Checker::is('creditCard', '4916375389940009', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4929167481032610', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4556242273553949', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4481007485188614', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4532800925229140', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4916845885268360', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4394514669078434', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '4485611378115042', ['check' => 'visa']))->toBe(true);
            expect(Checker::is('creditCard', '869940697287073', ['check' => 'visa']))->toBe(false);

            /* Visa Electron */
            expect(Checker::is('creditCard', '4175003346287100', ['check' => 'electron']))->toBe(true);
            expect(Checker::is('creditCard', '4175009797419290', ['check' => 'electron']))->toBe(true);
            expect(Checker::is('creditCard', '4175005028142917', ['check' => 'electron']))->toBe(true);
            expect(Checker::is('creditCard', '4913940802385364', ['check' => 'electron']))->toBe(true);
            expect(Checker::is('creditCard', '869940697287073', ['check' => 'electron']))->toBe(false);

            /* Voyager */
            expect(Checker::is('creditCard', '869940697287073', ['check' => 'voyager']))->toBe(true);
            expect(Checker::is('creditCard', '869934523596112', ['check' => 'voyager']))->toBe(true);
            expect(Checker::is('creditCard', '869958670174621', ['check' => 'voyager']))->toBe(true);
            expect(Checker::is('creditCard', '869921250068209', ['check' => 'voyager']))->toBe(true);
            expect(Checker::is('creditCard', '869972521242198', ['check' => 'voyager']))->toBe(true);
            expect(Checker::is('creditCard', '370482756063980', ['check' => 'voyager']))->toBe(false);

            expect(Checker::is('creditCard', '123', ['check' => 'any']))->toBe(false);
        });

        it("checks date values", function() {

            expect(Checker::is('date', date('Y-m-d')))->toBe(true);
            expect(Checker::is('date', new DateTime()))->toBe(true);

            expect(Checker::is('date', new stdClass()))->toBe(false);

        });

        it("checks date values before another date", function() {

            expect(Checker::is('dateBefore', '2015-07-19', ['date' => '2015-07-20']))->toBe(true);
            $date = DateTime::createFromFormat('Y-m-d', '2015-07-19');
            $date2 = DateTime::createFromFormat('Y-m-d', '2015-07-20');
            expect(Checker::is('dateBefore', $date, ['date' => $date2]))->toBe(true);

            expect(Checker::is('dateBefore', '2015-07-19', ['date' => '2015-07-18']))->toBe(false);
            $date3 = DateTime::createFromFormat('Y-m-d', '2015-07-18');
            expect(Checker::is('dateBefore', '2015-07-19', ['date' => $date3]))->toBe(false);

            expect(Checker::is('dateBefore', '2015-07-19'))->toBe(false);

        });

        it("checks date values after another date", function() {

            expect(Checker::is('dateAfter', '2015-07-19', ['date' => '2015-07-18']))->toBe(true);
            $date = DateTime::createFromFormat('Y-m-d', '2015-07-19');
            $date2 = DateTime::createFromFormat('Y-m-d', '2015-07-18');
            expect(Checker::is('dateAfter', $date, ['date' => $date2]))->toBe(true);

            expect(Checker::is('dateAfter', '2015-07-19', ['date' => '2015-07-20']))->toBe(false);
            $date3 = DateTime::createFromFormat('Y-m-d', '2015-07-20');
            expect(Checker::is('dateAfter', '2015-07-19', ['date' => $date3]))->toBe(false);

            expect(Checker::is('dateAfter', '2015-07-19'))->toBe(false);

        });

        it("checks values match a date format", function() {

            expect(Checker::is('dateFormat', '31-12-2012', ['format' => 'd-m-Y']))->toBe(true);
            expect(Checker::is('dateFormat', '1 1 1999', ['format' => 'd m Y']))->toBe(true);
            expect(Checker::is('dateFormat', '12/31/2012', ['format' => 'm/d/Y']))->toBe(true);
            expect(Checker::is('dateFormat', '02.29.2000', ['format' => 'm.d.Y']))->toBe(true);
            expect(Checker::is('dateFormat', '2012/12/31', ['format' => 'Y/m/d']))->toBe(true);
            expect(Checker::is('dateFormat', '1999-1-01', ['format' => 'Y-m-d']))->toBe(true);
            expect(Checker::is('dateFormat', '31 Dec 2012', ['format' => 'd M Y']))->toBe(true);
            expect(Checker::is('dateFormat', '1 January 1999', ['format' => 'd M Y']))->toBe(true);
            expect(Checker::is('dateFormat', 'Dec 31 2012', ['format' => 'M d Y']))->toBe(true);
            expect(Checker::is('dateFormat', 'January 1, 1999', ['format' => 'M d, Y']))->toBe(true);
            expect(Checker::is('dateFormat', 'December 2012', ['format' => 'M Y']))->toBe(true);
            expect(Checker::is('dateFormat', 'Jan 1999', ['format' => 'M Y']))->toBe(true);
            expect(Checker::is('dateFormat', '12/2012', ['format' => 'm/Y']))->toBe(true);
            expect(Checker::is('dateFormat', '1 2012', ['format' => 'm Y']))->toBe(true);
            expect(Checker::is('dateFormat', '1.January.1999', ['format' => 'd.M.Y']))->toBe(true);
            expect(Checker::is('dateFormat', 'Dec-31-2012', ['format' => 'M-d-Y']))->toBe(true);
            expect(Checker::is('dateFormat', 'December/2012', ['format' => 'M/Y']))->toBe(true);
            expect(Checker::is('dateFormat', 'Jan.1999', ['format' => 'M.Y']))->toBe(true);

            expect(Checker::is('dateFormat', '32-12-2012', ['format' => 'd-m-Y']))->toBe(false);
            expect(Checker::is('dateFormat', '29 2 1999', ['format' => 'd m Y']))->toBe(false);
            expect(Checker::is('dateFormat', '13/31/2012', ['format' => 'm/d/Y']))->toBe(false);
            expect(Checker::is('dateFormat', '1.0.1999', ['format' => 'm.d.Y']))->toBe(false);
            expect(Checker::is('dateFormat', '2012/11/31', ['format' => 'Y/m/d']))->toBe(false);
            expect(Checker::is('dateFormat', '2012/11/0', ['format' => 'Y/m/d']))->toBe(false);
            expect(Checker::is('dateFormat', '31 Dic 2012', ['format' => 'd M Y']))->toBe(false);
            expect(Checker::is('dateFormat', '13 2012', ['format' => 'm Y']))->toBe(false);

        });

        it("checks decimal values", function() {

            expect(Checker::is('decimal', '0.0'))->toBe(true);
            expect(Checker::is('decimal', '0.000'))->toBe(true);
            expect(Checker::is('decimal', '1.1'))->toBe(true);
            expect(Checker::is('decimal', '11.11'))->toBe(true);
            expect(Checker::is('decimal', '+0'))->toBe(true);
            expect(Checker::is('decimal', '-0'))->toBe(true);
            expect(Checker::is('decimal', '+1234.54321'))->toBe(true);
            expect(Checker::is('decimal', '-1234.54321'))->toBe(true);
            expect(Checker::is('decimal', '1234.54321'))->toBe(true);
            expect(Checker::is('decimal', '+0123.45e6'))->toBe(true);
            expect(Checker::is('decimal', '-0123.45e6'))->toBe(true);
            expect(Checker::is('decimal', '0123.45e6'))->toBe(true);
            expect(Checker::is('decimal', '1234'))->toBe(true);
            expect(Checker::is('decimal', '-1234'))->toBe(true);
            expect(Checker::is('decimal', '+1234'))->toBe(true);

            expect(Checker::is('decimal', 'string'))->toBe(false);

        });

        it("checks decimal with places values", function() {

            expect(Checker::is('decimal', '.27', ['precision' => '2']))->toBe(true);
            expect(Checker::is('decimal', .27, ['precision' => 2]))->toBe(true);
            expect(Checker::is('decimal', -.27, ['precision' => 2]))->toBe(true);
            expect(Checker::is('decimal', +.27, ['precision' => 2]))->toBe(true);
            expect(Checker::is('decimal', '.277', ['precision' => '3']))->toBe(true);
            expect(Checker::is('decimal', .277, ['precision' => 3]))->toBe(true);
            expect(Checker::is('decimal', -.277, ['precision' => 3]))->toBe(true);
            expect(Checker::is('decimal', +.277, ['precision' => 3]))->toBe(true);
            expect(Checker::is('decimal', '1234.5678', ['precision' => '4']))->toBe(true);
            expect(Checker::is('decimal', 1234.5678, ['precision' => 4]))->toBe(true);
            expect(Checker::is('decimal', -1234.5678, ['precision' => 4]))->toBe(true);
            expect(Checker::is('decimal', +1234.5678, ['precision' => 4]))->toBe(true);

            expect(Checker::is('decimal', '1234.5678', ['precision' => '3']))->toBe(false);
            expect(Checker::is('decimal', 1234.5678, ['precision' => 3]))->toBe(false);
            expect(Checker::is('decimal', -1234.5678, ['precision' => 3]))->toBe(false);
            expect(Checker::is('decimal', +1234.5678, ['precision' => 3]))->toBe(false);

        });

        it("checks emails values", function() {

            expect(Checker::is('equalTo', 'abcdef', [
                'key' => 'password_confirmation',
                'data'  => ['password_confirmation' => 'abcdef']
            ]))->toBe(true);

            expect(Checker::is('equalTo', 'abcdef', [
                'key' => 'password_confirmation',
                'data'  => ['password_confirmation' => 'defghi']
            ]))->toBe(false);
            expect(Checker::is('equalTo', 'abcdef', ['key' => 'password_confirmation']))->toBe(false);
            expect(Checker::is('equalTo', 'abcdef'))->toBe(false);

        });

        it("checks emails values", function() {

            expect(Checker::is('email', 'abc.efg@domain.com'))->toBe(true);
            expect(Checker::is('email', 'efg@domain.com'))->toBe(true);
            expect(Checker::is('email', 'abc-efg@domain.com'))->toBe(true);
            expect(Checker::is('email', 'abc_efg@domain.com'))->toBe(true);
            expect(Checker::is('email', 'raw@test.ra.ru'))->toBe(true);
            expect(Checker::is('email', 'abc-efg@domain-hyphened.com'))->toBe(true);
            expect(Checker::is('email', "p.o'malley@domain.com"))->toBe(true);
            expect(Checker::is('email', 'abc+efg@domain.com'))->toBe(true);
            expect(Checker::is('email', 'abc&efg@domain.com'))->toBe(true);
            expect(Checker::is('email', 'abc.efg@12345.com'))->toBe(true);
            expect(Checker::is('email', 'abc.efg@12345.co.jp'))->toBe(true);
            expect(Checker::is('email', 'abc@g.cn'))->toBe(true);
            expect(Checker::is('email', 'abc@x.com'))->toBe(true);
            expect(Checker::is('email', 'henrik@sbcglobal.net'))->toBe(true);
            expect(Checker::is('email', 'sani@sbcglobal.net'))->toBe(true);

            /* All ICANN TLDs */
            expect(Checker::is('email', 'abc@example.aero'))->toBe(true);
            expect(Checker::is('email', 'abc@example.asia'))->toBe(true);
            expect(Checker::is('email', 'abc@example.biz'))->toBe(true);
            expect(Checker::is('email', 'abc@example.cat'))->toBe(true);
            expect(Checker::is('email', 'abc@example.com'))->toBe(true);
            expect(Checker::is('email', 'abc@example.coop'))->toBe(true);
            expect(Checker::is('email', 'abc@example.edu'))->toBe(true);
            expect(Checker::is('email', 'abc@example.gov'))->toBe(true);
            expect(Checker::is('email', 'abc@example.info'))->toBe(true);
            expect(Checker::is('email', 'abc@example.int'))->toBe(true);
            expect(Checker::is('email', 'abc@example.jobs'))->toBe(true);
            expect(Checker::is('email', 'abc@example.mil'))->toBe(true);
            expect(Checker::is('email', 'abc@example.mobi'))->toBe(true);
            expect(Checker::is('email', 'abc@example.museum'))->toBe(true);
            expect(Checker::is('email', 'abc@example.name'))->toBe(true);
            expect(Checker::is('email', 'abc@example.net'))->toBe(true);
            expect(Checker::is('email', 'abc@example.org'))->toBe(true);
            expect(Checker::is('email', 'abc@example.pro'))->toBe(true);
            expect(Checker::is('email', 'abc@example.tel'))->toBe(true);
            expect(Checker::is('email', 'abc@example.travel'))->toBe(true);
            expect(Checker::is('email', 'someone@st.t-com.hr'))->toBe(true);

            /* Strange, but valid addresses*/
            expect(Checker::is('email', '_somename@example.com'))->toBe(true);
            expect(Checker::is('email', 'abc@example.c'))->toBe(true);
            expect(Checker::is('email', 'abc@example.com.a'))->toBe(true);
            expect(Checker::is('email', 'abc@example.toolong'))->toBe(true);

            /* Invalid addresses */
            expect(Checker::is('email', 'abc@example.com.'))->toBe(false);
            expect(Checker::is('email', 'abc@example..com'))->toBe(false);
            expect(Checker::is('email', 'abc;@example.com'))->toBe(false);
            expect(Checker::is('email', 'abc@example.com;'))->toBe(false);
            expect(Checker::is('email', 'abc@efg@example.com'))->toBe(false);
            expect(Checker::is('email', 'abc@@example.com'))->toBe(false);
            expect(Checker::is('email', 'abc efg@example.com'))->toBe(false);
            expect(Checker::is('email', 'abc,efg@example.com'))->toBe(false);
            expect(Checker::is('email', 'abc@sub,example.com'))->toBe(false);
            expect(Checker::is('email', "abc@sub'example.com"))->toBe(false);
            expect(Checker::is('email', 'abc@sub/example.com'))->toBe(false);
            expect(Checker::is('email', 'abc@yahoo!.com'))->toBe(false);
            expect(Checker::is('email', "Nyrée.surname@example.com"))->toBe(false);
            expect(Checker::is('email', 'abc@example_underscored.com'))->toBe(false);
            expect(Checker::is('email', 'raw@test.ra.ru....com'))->toBe(false);

        });

        it("deeply checks emails", function() {

            Monkey::patch('getmxrr', function() {
                return true;
            });
            expect(Checker::is('email', 'abc.efg@rad-dev.org', ['deep' => true]))->toBe(true);

            Monkey::patch('getmxrr', function() {
                return false;
            });
            expect(Checker::is('email', 'abc.efg@invalidfoo.com', ['deep' => true]))->toBe(false);

        });

        it("checks in list values", function() {

            expect(Checker::is('inList', 'one', ['list' => ['one', 'two']]))->toBe(true);
            expect(Checker::is('inList', 'two', ['list' => ['one', 'two']]))->toBe(true);
            expect(Checker::is('inList', 0, ['list' => [0, 1]]))->toBe(true);
            expect(Checker::is('inList', 1, ['list' => [0, 1]]))->toBe(true);
            expect(Checker::is('inList', 0, ['list' => ['0', '1']]))->toBe(true);
            expect(Checker::is('inList', '1', ['list' => ['0', '1']]))->toBe(true);
            expect(Checker::is('inList', 1, ['list' => ['0', '1']]))->toBe(true);
            expect(Checker::is('inList', '1', ['list' => ['0', '1']]))->toBe(true);

            expect(Checker::is('inList', '', ['list' => ['0', '1']]))->toBe(false);
            expect(Checker::is('inList', null, ['list' => ['0', '1']]))->toBe(false);
            expect(Checker::is('inList', false, ['list' => ['0', '1']]))->toBe(false);
            expect(Checker::is('inList', true, ['list' => ['0', '1']]))->toBe(false);

            expect(Checker::is('inList', '', ['list' => [0, 1]]))->toBe(false);
            expect(Checker::is('inList', null, ['list' => [0, 1]]))->toBe(false);
            expect(Checker::is('inList', false, ['list' => [0, 1]]))->toBe(false);
            expect(Checker::is('inList', true, ['list' => [0, 1]]))->toBe(false);

            expect(Checker::is('inList', 2, ['list' => [0, 1]]))->toBe(false);
            expect(Checker::is('inList', 2, ['list' => ['0', '1']]))->toBe(false);
            expect(Checker::is('inList', '2', ['list' => [0, 1]]))->toBe(false);
            expect(Checker::is('inList', '2', ['list' => ['0', '1']]))->toBe(false);
            expect(Checker::is('inList', 'three', ['list' => ['one', 'two']]))->toBe(false);
        });

        it("checks in range values", function() {

            $lower = 1;
            $upper = 10;

            $value = 0;
            $result = Checker::is('inRange', $value, compact('lower', 'upper'));
            expect($result)->toBe(false);

            $value = 1;
            $result = Checker::is('inRange', $value, compact('lower', 'upper'));
            expect($result)->toBe(true);

            $value = 5;
            $result = Checker::is('inRange', $value, compact('lower', 'upper'));
            expect($result)->toBe(true);

            $value = 10;
            $result = Checker::is('inRange', $value, compact('lower', 'upper'));
            expect($result)->toBe(true);

            $value = 11;
            $result = Checker::is('inRange', $value, compact('lower', 'upper'));
            expect($result)->toBe(false);

            $value = 'abc';
            $result = Checker::is('inRange', $value, compact('lower', 'upper'));
            expect($result)->toBe(false);

            $result = Checker::is('inRange', -1, ['upper' => 1]);
            expect($result)->toBe(true);

            $result = Checker::is('inRange', 1, ['upper' => 1]);
            expect($result)->toBe(true);

            $result = Checker::is('inRange', 2, ['upper' => 1]);
            expect($result)->toBe(false);

            $result = Checker::is('inRange', 2, ['lower' => 1]);
            expect($result)->toBe(true);

            $result = Checker::is('inRange', 1, ['lower' => 1]);
            expect($result)->toBe(true);

            $result = Checker::is('inRange', 0, ['lower' => 1]);
            expect($result)->toBe(false);

            expect(Checker::is('inRange', 0))->toBe(true);
        });

        it("checks integer values", function() {

            expect(Checker::is('integer', '27'))->toBe(true);
            expect(Checker::is('integer', '-27'))->toBe(true);
            expect(Checker::is('integer', '+27'))->toBe(true);
            expect(Checker::is('integer', 27))->toBe(true);
            expect(Checker::is('integer', -27))->toBe(true);
            expect(Checker::is('integer', +27))->toBe(true);

            expect(Checker::is('integer', .277))->toBe(false);
            expect(Checker::is('integer', '1234.5678'))->toBe(false);
            expect(Checker::is('integer', 'abcd'))->toBe(false);

        });

        it("checks ip values", function() {

            expect(Checker::is('ip', '127.0.0.1', ['contains' => false]))->toBe(true);

        });

        it("checks values matching length", function() {

            expect(Checker::is('length', 'abcde', ['length' => 5]))->toBe(true);

            expect(Checker::is('length', 'abcde', ['length' => 4]))->toBe(false);
            expect(Checker::is('length', 'abcde'))->toBe(false);

        });

        it("checks values matching length between", function() {

            expect(Checker::is('lengthBetween', 'abcde', ['min' => 1, 'max' => 7]))->toBe(true);
            expect(Checker::is('lengthBetween', '', ['min' => 0, 'max' => 7]))->toBe(true);

            expect(Checker::is('lengthBetween', 'abcd', ['min' => 1, 'max' => 3]))->toBe(false);

        });

        it("checks values matching max length", function() {

            expect(Checker::is('lengthMax', 'abcde', ['length' => 7]))->toBe(true);

            expect(Checker::is('lengthMax', 'abcd', ['length' => 3]))->toBe(false);
            expect(Checker::is('lengthMax', ''))->toBe(false);

        });

        it("checks values matching min length", function() {

            expect(Checker::is('lengthMin', 'abcde', ['length' => 1]))->toBe(true);

            expect(Checker::is('lengthMin', '', ['length' => 1]))->toBe(false);
            expect(Checker::is('lengthMin', 'abcd'))->toBe(false);

        });

        it("checks luhn values", function() {

            expect(Checker::is('luhn', '869972521242198'))->toBe(true);

            expect(Checker::is('luhn', false))->toBe(false);
            expect(Checker::is('luhn', null))->toBe(false);
            expect(Checker::is('luhn', ''))->toBe(false);
            expect(Checker::is('luhn', true))->toBe(false);

        });

        it("checks values matching max", function() {

            expect(Checker::is('max', 5, ['max' => 7]))->toBe(true);

            expect(Checker::is('max', 5, ['max' => 3]))->toBe(false);
            expect(Checker::is('max', 5))->toBe(false);

        });

        it("checks values matching min", function() {

            expect(Checker::is('min', 5, ['min' => 1]))->toBe(true);

            expect(Checker::is('min', 3, ['min' => 5]))->toBe(false);
            expect(Checker::is('min', 5))->toBe(false);

        });

        it("checks money values", function() {

            expect(Checker::is('money', '3.25'))->toBe(true);
            expect(Checker::is('money', '3.25€'))->toBe(true);
            expect(Checker::is('money', '$3.25'))->toBe(true);
            expect(Checker::is('money', '3.25€', ['check' => 'right']))->toBe(true);
            expect(Checker::is('money', '$3.25', ['check' => 'left']))->toBe(true);

            expect(Checker::is('money', '325a'))->toBe(false);
            expect(Checker::is('money', '3.25€', ['check' => 'left']))->toBe(false);
            expect(Checker::is('money', '$3.25', ['check' => 'right']))->toBe(false);

        });

        it("checks not empty values", function() {

            expect(Checker::is('not:empty', 'abcdefg'))->toBe(true);
            expect(Checker::is('not:empty', 'fasdf '))->toBe(true);
            expect(Checker::is('not:empty', 'fooo' . chr(243) . 'blabla'))->toBe(true);
            expect(Checker::is('not:empty', 'abçďĕʑʘπй'))->toBe(true);
            expect(Checker::is('not:empty', 'José'))->toBe(true);
            expect(Checker::is('not:empty', 'é'))->toBe(true);
            expect(Checker::is('not:empty', 'π'))->toBe(true);

            expect(Checker::is('not:empty', "\t "))->toBe(false);
            expect(Checker::is('not:empty', ""))->toBe(false);
        });

        it("checks numeric values", function() {

            expect(Checker::is('numeric', 0))->toBe(true);
            expect(Checker::is('numeric', '0'))->toBe(true);
            expect(Checker::is('numeric', '-0'))->toBe(true);
            expect(Checker::is('numeric', '-'))->toBe(false);

        });

        it("checks phone values", function() {

            expect(Checker::is('numeric', '1234567890'))->toBe(true);
            expect(Checker::is('numeric', '+1234567890'))->toBe(true);

        });

        it("checks regexp values", function() {

            expect(Checker::is('regex', '/^123$/'))->toBe(true);
            expect(Checker::is('regex', '/^abc$/'))->toBe(true);
            expect(Checker::is('regex', '/^abc123$/'))->toBe(true);
            expect(Checker::is('regex', '@^abc$@'))->toBe(true);
            expect(Checker::is('regex', '#^abc$#'))->toBe(true);

            expect(Checker::is('regex', '(^abc$)'))->toBe(true);
            expect(Checker::is('regex', '{^abc$}'))->toBe(true);
            expect(Checker::is('regex', '[^abc$]'))->toBe(true);
            expect(Checker::is('regex', '<^abc$>'))->toBe(true);
            expect(Checker::is('regex', ')^abc$)'))->toBe(true);
            expect(Checker::is('regex', '}^abc$}'))->toBe(true);
            expect(Checker::is('regex', ']^abc$]'))->toBe(true);
            expect(Checker::is('regex', '>^abc$>'))->toBe(true);

            expect(Checker::is('regex', 'd^abc$d'))->toBe(false);
            expect(Checker::is('regex', '\\^abc$\\'))->toBe(false);
            expect(Checker::is('regex', '(^abc$('))->toBe(false);
            expect(Checker::is('regex', '{^abc${'))->toBe(false);
            expect(Checker::is('regex', '[^abc$['))->toBe(false);
            expect(Checker::is('regex', '<^abc$<'))->toBe(false);

        });

        it("checks time values", function() {

            expect(Checker::is('time', '07:15:00'))->toBe(true);
            expect(Checker::is('time', '19:15:00'))->toBe(true);
            expect(Checker::is('time', '7:15:00AM'))->toBe(true);
            expect(Checker::is('time', '07:15:00pm'))->toBe(true);

            expect(Checker::is('time', '07:615:00'))->toBe(false);
        });

        it("checks url values", function() {

            expect(Checker::is('url', 'http://example.com'))->toBe(true);
            expect(Checker::is('url', 'http://www.domain.com/super?param=value'))->toBe(true);

            expect(Checker::is('url', 'http:/example.com'))->toBe(false);

        });

        it("checks uuid values", function() {

            expect(Checker::is('uuid', '1c0a5830-6025-11de-8a39-0800200c9a66'))->toBe(true);
            expect(Checker::is('uuid', '1c0a5831-6025-11de-8a39-0800200c9a66'))->toBe(true);
            expect(Checker::is('uuid', '1c0a5832-6025-11de-8a39-0800200c9a66'))->toBe(true);

            expect(Checker::is('uuid', 'zc0a5832-6025-11de-8a39-0800200c9a66'))->toBe(false);
            expect(Checker::is('uuid', '1-1c0a5832-6025-11de-8a39-0800200c9a66'))->toBe(false);

        });

        it("checks the not option", function() {

            expect(Checker::is('inList', 'one', ['list' => ['one', 'two']]))->toBe(true);
            expect(Checker::is('not:inList', 'one', ['list' => ['one', 'two']]))->toBe(false);
            expect(Checker::isNotInList('one', ['list' => ['one', 'two']]))->toBe(false);

        });

    });

    describe("::__callStatic()", function() {

        it("delegates to the checker", function() {

            $handler = Checker::get('alphaNumeric');
            expect(Checker::class)->toReceive('::check')->with('frferrf', [$handler] , [
                'hello' => 'world'
            ]);

            Checker::isAlphaNumeric('frferrf', ['hello' => 'world']);

        });

        it("bails out with no passed parameters", function() {

            expect(Checker::isAlphaNumeric())->toBe(false);

        });
    });

    describe("::message()", function() {

        it("gets a error message", function() {

            expect(Checker::message('required'))->toBe('is required');

        });

        it("sets an error message", function() {

            expect(Checker::message('required', 'must be defined'))->toBe('must be defined');
            expect(Checker::message('required'))->toBe('must be defined');

        });
    });

    describe("::messages()", function() {

        beforeEach(function() {
            Checker::messages(['a' => 'b'], false);
        });

        it("gets error messages", function() {

            expect(Checker::messages())->toBe(['a' => 'b', '_default_' => 'is invalid']);

        });

        it("appends error messages", function() {

            $expected = ['a' => 'b', '_default_' => 'is invalid', 'c' => 'd'];
            expect(Checker::messages(['c' => 'd']))->toBe($expected);
            expect(Checker::messages())->toBe($expected);

        });

        it("sets error messages", function() {

            $expected = ['c' => 'd', '_default_' => 'is invalid'];
            expect(Checker::messages(['c' => 'd'], false))->toBe($expected);
            expect(Checker::messages())->toBe($expected);

        });
    });

});
