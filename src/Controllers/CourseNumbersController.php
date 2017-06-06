<?php
namespace CS4450\Controllers;

use PDO;
use CS4450\Http\StatusCodes;
use CS4450\Utilities\DatabaseConnection;

class CourseNumbersController
{
	public static function getAllCoursesBasedOnDepartmentList(){
		$db = DatabaseConnection::getInstance();
		$data = (object)json_decode(file_get_contents('php://input'));
		
		$departments = $data->departments;
		
		if(empty($departments)){
			http_response_code(StatusCodes::BAD_REQUEST);
            die();
		}
		
		
		$query = '
            SELECT DISTINCT ct.courseNumber AS CourseNumber, ct.subjectCode AS Subject
            FROM CourseTitles ct
            INNER JOIN CourseSections cs
            ON ct.subjectCode = cs.subjectCode
            INNER JOIN Departments d
            ON d.code = ct.departmentCode
			WHERE 
			ct.departmentCode LIKE :dept0
		';
		
		for($i=1; $i<count($departments); $i++){
			$query .= ' 
			OR ct.departmentCode LIKE :dept' . $i;
		}
		
		$query .= '
		ORDER BY Subject ASC, courseNumber ASC';

		
		$stmt = $db->prepare($query);
		for($i=0; $i<count($departments); $i++){
			$stmt->bindValue(':dept' . $i, $departments[$i]->DepartmentCode);
		}

		$stmt->execute();
		
		$returnData = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		for($i=0; $i<count($returnData); $i++){
			$returnData[$i]["Index"] = 1000000 + $i;
		}

		return $returnData;
	}
}
