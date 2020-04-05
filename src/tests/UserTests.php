<?php


use PHPUnit\Framework\TestCase;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

require_once __DIR__."/../../config/config.php";
require_once __DIR__."/../../config/Database.php";
require_once __DIR__."/../models/User.php";
require_once __DIR__."/../controllers/UserController.php";
require_once __DIR__."/../helper/JsonResponse.php";
require_once __DIR__."/TestHelper.php";


final class UserTests extends TestCase {

	public function testCanGetAll() : void {
		$response = TestHelper::http("GET", UNIT_TEST_BASE_URL."users");

		if(!isset($response['users'])) {
			$this->fail();
		} else {
			foreach($response['users'] as $user) {
				if((($user['id'] ?? null)                 && gettype($user['id'])                 !== "integer") ||
				   (($user['parent_id'] ?? null)          && gettype($user['parent_id'])          !== "integer") ||
				   (($user['type'] ?? null)               && UserType::isValidName($user['type']))               ||
				   (($user['age_classification'] ?? null) && gettype($user['age_classification']) !== "string")  ||
				   (($user['first_name'] ?? null)         && gettype($user['first_name'])         !== "string")  ||
				   (($user['last_name'] ?? null)          && gettype($user['last_name'])          !== "string")  ||
				   (($user['email'] ?? null)              && gettype($user['email'])              !== "string")  ||
				   (($user['password'] ?? null)           && gettype($user['password'])           !== "string")  ||
				   (($user['credit'] ?? null)             && gettype($user['credit'])             !== "double")  ||
				   (($user['birthday'] ?? null)           && gettype($user['birthday'])           !== "string")  ||
				   (($user['signature'] ?? null)          && gettype($user['signature'])          !== "string")
				) {
					$this->fail();
					break;
				}
			}
		}

		$this->assertEquals(true,true);
	}

	public function testCanGetById() : void {
		$response = TestHelper::http("GET", UNIT_TEST_BASE_URL."users/1");

		$expected_response = [
			"user" => [
				"id" => 1,
				"parent_id" => null,
				"type" => "SUPER_ADMIN",
				"age_classification"=> "ADULT",
				"first_name" => "Seth",
				"last_name" => "Campbell",
				"email" => "sethrcamp@gmail.com",
				"password" => "HASHED_PASSWORD",
				"credit" => 0,
				"birthday" => "1998-08-14",
				"signature" => null
 		    ]
		];

		$this->assertEquals($expected_response, $response);
	}


}