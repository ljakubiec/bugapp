<?php
// Call Model_UserTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Model_UserTest::main");
}

require_once dirname(__FILE__) . '/../TestHelper.php';

/** Model_User */
require_once 'User.php';

/**
 * Test class for Model_User.
 *
 * @group Models
 */
class Model_UserTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Model_UserTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->model = new Model_User();
        $this->truncateTable();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
    }

    public function truncateTable()
    {
        $this->model->getTable('user')->getAdapter()->getConnection()->exec('DELETE FROM user');
    }

    public function testShouldNotAllowAddingUsersWithExistingUsername()
    {
        $id = $this->model->save(array(
            'username' => 'foo',
            'email'    => 'foo@email.com',
            'fullname' => 'Foo Bar',
            'password' => md5('foobar'),
        ));

        try {
            $test = $this->model->save(array(
                'username' => 'foo',
                'email'    => 'foo2@email.com',
                'fullname' => 'Foo Bar',
                'password' => md5('foobar'),
            ));
            $this->fail('Users with duplicate names should raise exceptions');
        } catch (Exception $e) {
            $this->assertContains('duplicate', $e->getMessage());
        }
    }

    public function testShouldNotAllowAddingUsersWithExistingEmail()
    {
        $id = $this->model->save(array(
            'username' => 'foo',
            'email'    => 'foo@email.com',
            'fullname' => 'Foo Bar',
            'password' => md5('foobar'),
        ));

        try {
            $test = $this->model->save(array(
                'username' => 'foo2',
                'email'    => 'foo@email.com',
                'fullname' => 'Foo Bar',
                'password' => md5('foobar'),
            ));
            $this->fail('Users with duplicate names should raise exceptions');
        } catch (Exception $e) {
            $this->assertContains('duplicate', $e->getMessage());
        }
    }

    public function testFetchUserShouldAllowFetchingByUsername()
    {
        $id = $this->model->save(array(
            'username' => 'foo',
            'email'    => 'foo@email.com',
            'fullname' => 'Foo Bar',
            'password' => md5('foobar'),
        ));

        $user = $this->model->fetchUser('foo');
        $this->assertEquals($id, $user->id);
    }

    public function testFetchUserShouldAllowFetchingByEmail()
    {
        $id = $this->model->save(array(
            'username' => 'foo',
            'email'    => 'foo@email.com',
            'fullname' => 'Foo Bar',
            'password' => md5('foobar'),
        ));

        $user = $this->model->fetchUser('foo@email.com');
        $this->assertEquals($id, $user->id);
    }

    public function testShouldAllowBanningUsers()
    {
        $id = $this->model->save(array(
            'username' => 'foo',
            'email'    => 'foo@email.com',
            'fullname' => 'Foo Bar',
            'password' => md5('foobar'),
        ));

        $user = $this->model->fetchUser('foo@email.com');

        $this->model->ban($id);

        $test = $this->model->fetchUser('foo@email.com');
        $this->assertNotSame($user, $test);
        $this->assertNull($test);
    }
}

// Call Model_UserTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Model_UserTest::main") {
    Model_UserTest::main();
}
