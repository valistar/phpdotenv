<?php
class DotenvTest extends \PHPUnit_Framework_TestCase
{
    public function testCommentedDotenvLoadsEnvironmentVars()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures', 'commented.env');
        $this->assertEquals('bar', $_ENV['CFOO']);
        $this->assertEquals(false, array_key_exists('CBAR', $_ENV));
        $this->assertEquals(false, array_key_exists('CZOO', $_ENV));
        $this->assertEquals('with spaces', $_ENV['CSPACED']);
        $this->assertEquals('a value with a # character', $_ENV['CQUOTES']);
        $this->assertEquals('a value with a # character & a quote " character inside quotes', $_ENV['CQUOTESWITHQUOTE']);
        $this->assertEquals('', $_ENV['CNULL']);
    }

    public function testQuotedDotenvLoadsEnvironmentVars()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures', 'quoted.env');
        $this->assertEquals('bar', $_ENV['QFOO']);
        $this->assertEquals('baz', $_ENV['QBAR']);
        $this->assertEquals('with spaces', $_ENV['QSPACED']);
        $this->assertEquals('', $_ENV['QNULL']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_ENV['QEQUALS']);
        $this->assertEquals("test some escaped characters like a quote (') or maybe a backslash (\\)", $_ENV['QESCAPED']);
    }

    public function testExportedDotenvLoadsEnvironmentVars()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures', 'exported.env');
        $this->assertEquals('bar', $_ENV['EFOO']);
        $this->assertEquals('baz', $_ENV['EBAR']);
        $this->assertEquals('with spaces', $_ENV['ESPACED']);
        $this->assertEquals('', $_ENV['ENULL']);
    }

    public function testDotenvLoadsEnvGlobals()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures');
        $this->assertEquals('bar', $_SERVER['FOO']);
        $this->assertEquals('baz', $_SERVER['BAR']);
        $this->assertEquals('with spaces', $_SERVER['SPACED']);
        $this->assertEquals('', $_SERVER['NULL']);
    }

    public function testDotenvLoadsServerGlobals()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures');
        $this->assertEquals('bar', $_ENV['FOO']);
        $this->assertEquals('baz', $_ENV['BAR']);
        $this->assertEquals('with spaces', $_ENV['SPACED']);
        $this->assertEquals('', $_ENV['NULL']);
    }

    public function testDotenvRequiredStringEnvironmentVars()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures');
        $res = Dotenv::required('FOO');
        $this->assertTrue($res);
    }

    public function testDotenvRequiredArrayEnvironmentVars()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures');
        $res = Dotenv::required(array('FOO', 'BAR'));
        $this->assertTrue($res);
    }

    public function testDotenvNestedEnvironmentVars()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures', 'nested.env');
        $this->assertEquals('Hello World!', $_ENV['NVAR3']);
        $this->assertEquals('${NVAR1} ${NVAR2}', $_ENV['NVAR4']); // not resolved
        $this->assertEquals('$NVAR1 {NVAR2}', $_ENV['NVAR5']); // not resolved
    }

    public function testDotenvAllowedValues()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures');
        $res = Dotenv::required('FOO', array('bar', 'baz'));
        $this->assertTrue($res);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Required environment variable missing, or value not allowed: 'FOO'
     */
    public function testDotenvProhibitedValues()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures');
        $res = Dotenv::required('FOO', array('buzz'));
        $this->assertTrue($res);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Required environment variable missing, or value not allowed: 'FOOX', 'NOPE'
     */
    public function testDotenvRequiredThrowsRuntimeException()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures');
        $res = Dotenv::required(array('FOOX', 'NOPE'));
    }

    public function testDotenvNullFileArgumentUsesDefault()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures', null);

        $this->assertEquals('bar', $_ENV['FOO']);
    }

    /**
     * The fixture data has whitespace between the key and in the value string
     *     Test that these keys are trimmed down
     */
    public function testDotenvTrimmedKeys()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures', 'quoted.env');
        $this->assertTrue(isset($_ENV['QWHITESPACE']));
    }

    public function testDotenvDoesNotOverwriteEnvWhenImmutable()
    {
        Dotenv::makeMutable(); // only need this because we've previously set the variable
        Dotenv::setEnvironmentVariable('QFOO=external');
        Dotenv::makeImmutable();
        Dotenv::load(dirname(__DIR__) . '/fixtures', 'quoted.env');
        $this->assertEquals('external', $_ENV['QFOO']);
    }

    public function testDotenvDoesNotOverwriteEnvWhenMutable()
    {
        Dotenv::makeMutable();
        Dotenv::setEnvironmentVariable('QFOO=external');
        Dotenv::load(dirname(__DIR__) . '/fixtures', 'quoted.env');
        $this->assertEquals('bar', $_ENV['QFOO']);
    }

    public function testDotenvAllowsSpecialCharacters()
    {
        Dotenv::load(dirname(__DIR__) . '/fixtures', 'specialchars.env');
        $this->assertEquals('$a6^C7k%zs+e^.jvjXk', $_ENV['SPVAR1']);
        $this->assertEquals('?BUty3koaV3%GA*hMAwH}B', $_ENV['SPVAR2']);
        $this->assertEquals('jdgEB4{QgEC]HL))&GcXxokB+wqoN+j>xkV7K?m$r', $_ENV['SPVAR3']);
        $this->assertEquals('22222:22#2^{', $_ENV['SPVAR4']);
        $this->assertEquals("test some escaped characters like a quote \\' or maybe a backslash \\\\", $_ENV['SPVAR5']);
    }

    public function testDotenvImmutableStatus()
    {
        Dotenv::makeImmutable();
        $this->assertTrue( Dotenv::isImmutable() );
        Dotenv::makeMutable();
        $this->assertFalse( Dotenv::isImmutable() );
    }
}
