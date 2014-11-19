<?php
require 'StudentsDAO.php';
require 'PodcastsDAO.php';
require 'RecordsDAO.php';

class Database {
	//------------------------------------------
	public static function getNumRows($result) {
		return $result->num_rows;
	}
	//------------------------------------------
	public static function getNextRow($result) {
		return $result->fetch_array();
	}

	//---------------------------------------
	public static function queryAlert($sql) {
		echo "<script>alert(\"$sql\");</script>";
	}
	//---------------
	var $studentsDAO;
	var $podcastsDAO;
	var $recordsDAO;
	var $mysqli;
	//-------------------
	function Database() {
		$this->mysqli = new mysqli("localhost","reports","tillam00kb33f","phpmyadmin") or die(mysql_error());
		if ($this->mysqli->connect_errno) {
		  echo "Failed to connect to MySQL: " . $mysqli->connect_error();
		  exit();
		}
		$this->mysqli->autocommit(false);
		$this->studentsDAO = new StudentsDAO($this->mysqli);
		$this->podcastsDAO = new PodcastsDAO($this->mysqli);
		$this->recordsDAO = new RecordsDAO($this->mysqli);
	}
	//--------------------------
	function &getStudentsDAO() {
		return $this->studentsDAO;
	}
	//--------------------------
	function &getPodcastsDAO() {
		return $this->podcastsDAO;
	}
	//-------------------------
	function &getRecordsDAO() {
		return $this->recordsDAO;
	}
	//-----------------------
	function close($commit) {
		if ($commit)
			$this->mysqli->commit();
		else {
			echo "MySQL transaction failed: " + $this->getError();
			$this->mysqli->rollback();
		}
		$this->mysqli->close();
		return;
	}
	//--------------------------
	public function getError() {
		return $this->mysqli->connect_error;
	}
	
	//---------------------------------------
	public function getCurrentWeek() {
		$sql = 'SELECT WEEK(NOW()) AS `current_week`';
		$result = $this->mysqli->query($sql);
		return $this->getNextRow($result)['current_week'];
	}
	//--------------------------------------------------------------
	public function getCurrentGradeWeekFromBase($base_date) {
		$sql = "SELECT WEEK('$base_date') AS `base_week`";
		$result = $this->mysqli->query($sql);
		$base_week = $this->getNextRow($result)['base_week'];
		$current_week = $this->getCurrentWeek();
		return $current_week > $base_week ? $current_week - $base_week : 0;
	}
	//------------------------------------------------
	// Function to get the next grade week for a student
	// This is necessary if a student watches more than one podcast in a week to make sure no grade week gets duplicated.
	public function getNextGradeWeek($net_id) {
		global $BASE_DATE;
		$grade_week = $this->getCurrentGradeWeekFromBase($BASE_DATE) - 1;
		while ($this->recordsDAO->existsPodcastWatchedRecordForStudentAndGradeWeek($net_id, ++$grade_week));
		return $grade_week;
	}
}
?>




















