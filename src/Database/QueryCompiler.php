<?php
namespace Tiga\Framework\Database;
use Tiga\Framework\Database\Raw as Raw;

class QueryCompiler {

	private $query;

	private $queryString = "";
	
	private $previousQueryString = "";

	private $rawQuery = false;

	function __construct($connection) {

		$this->connection = $connection;

		$this->query = new Query();

	}


	function getQuery() {

		return $this->query;

	}

	function setQuery($query) {

		if($query instanceof Query) {

			$this->query = $query;

			return;
		}

		if($query instanceof Raw){
			
			$this->rawQuery = true;
			$this->queryString = $query->getString();


			return;			
		}

		$this->rawQuery = true;
		$this->queryString = $query ;
	

	}

	function getString() {
		return $this->queryString;
	}

	function setString($queryString) {
		$this->queryString = $queryString;
	}

	function getCompiledQuery() {

		return $this->queryString;

	}

	function lastQuery() {
		return $this->lastQuery;
	}

	/*
	 * Compile the query
	 */
	function compile($type="select") {

		// Raw Type
		if($this->rawQuery) {
			// Bind value
			$this->bindValue();

			return $this->queryString;	

		}

		$this->queryString = '';

		if($type=='select') {
			
			$this->compileSelect();
			$this->compileFrom();
			$this->compileJoin();
			$this->compileWhere();
			$this->compileGroupBy();
			$this->compileOrderBy();
			$this->compileOffset();

		}
		elseif($type=='count') {

			$this->compileCount();
			$this->compileFrom();
			$this->compileJoin();
			$this->compileWhere();
			$this->compileGroupBy();

		}
		elseif($type=='insert') {
		
			$this->compileInsert();
		
		}
		elseif($type=='update') {
		
			$this->compileUpdate();
			$this->compileJoin();
			$this->compileWhere();
		
		}
		elseif($type=='delete') {
		
			$this->compileDelete();
			$this->compileFrom();
			$this->compileJoin();
			$this->compileWhere();
			$this->compileGroupBy();
		
		}

		// Bind value
		$this->bindValue();

		return $this->queryString;
	}

	function bindValue() {

		$bind = $this->query->getBind();

		foreach ($bind as $key => $value) {
			if($key=='?')
				$this->queryString = preg_replace('/?/', $value, $this->queryString, 1);
			else
				$this->queryString = str_replace($key,$value,$this->queryString);
		}
	}

	/*
	 * Compile Selector, Select, Insert,Update,Delete,Count
	 */
	function compileSelect() {

		// Compile "Select From"
		if(sizeof($this->query->getQuerySelect())==0){
			$this->queryString = "SELECT * ";

			return;
		}


		foreach ($this->query->getQuerySelect() as $key=>$value) {

			if($value instanceof Raw)
				$value = $value->getString();

			if($key==0){
				$selectString = "$value";
			}
			else{
				$selectString .= ",$value";
			}
			
		}

		$this->queryString = "SELECT $selectString";
		
	}

	function compileDelete() {

		$this->queryString = "DELETE ";

	}

	function compileInsert() {

		$column = "";
		
		$insertValue = "";

		foreach ($this->query->getInsert() as $key => $value) {
			
			$column="$column $key,";

			$insertValue="$insertValue $value,";
		}	

		$column = rtrim($column, ",");
		$insertValue = rtrim($insertValue, ",");

		$table = $this->query->getQueryFrom()[0];

		$this->queryString = "INSERT INTO $table ($column) VALUES ($insertValue)";

	}

	function compileUpdate() {

		$updateStatement ='';

		foreach ($this->query->getUpdate() as $key => $value) {
		
			$updateStatement="$updateStatement $key = $value,";
		
		}	

		$updateStatement = rtrim($updateStatement,',');

		$table = $this->query->getQueryFrom()[0];

		$this->queryString = "UPDATE $table  ";

		$this->queryString = "{$this->queryString} SET $updateStatement";

	}

	function compileCount() {

		$this->queryString = "SELECT COUNT(*) as count {$this->queryString}";

	}

	/*
	 * Compile Query
	 */

	function compileFrom() {

		$fromQuery = "";

		foreach ($this->query->getQueryFrom() as $key=>$value) {

			if($key==0)
				$fromQuery = "$value";
			else
				$fromQuery .= ",$value";
			
		}

		$this->queryString = "{$this->queryString} FROM $fromQuery";

	}


	function compileJoin() {

		foreach ($this->query->getQueryJoin() as $join) {

			if($join['raw']==false)
				$queryString = "{$join['joinType']} JOIN {$join['table']} on {$join['leftColumn']} {$join['operator']} {$join['rightColumn']}";

			else 
				$queryString = $join['join']->getString();

			$this->queryString = "{$this->queryString} $queryString";

		}
			
	}

	function compileWhere() {

		$where = 'WHERE';

		foreach ($this->query->getQueryWhere() as $key => $value) {

			if($key==0)
				$this->queryString = "{$this->queryString} $where {$value['column']} {$value['operator']} {$value['value']}";
			else
				$this->queryString = "{$this->queryString} $where {$value['condition']} {$value['column']} {$value['operator']} {$value['value']} ";

			$where ='';
			
		}

		//the custom query is the only where query, remove the ( )
		$singleWhere=false;
		if(sizeof($this->query->getQueryWhere())==0&&sizeof($this->query->getQueryWhereRaw())==1)
			$singleWhere = true;
		
		foreach ($this->query->getQueryWhereRaw() as $key => $value) {

			if($singleWhere)
				$this->queryString = "{$this->queryString} $where {$value['query']}";
			
			else
				$this->queryString = "{$this->queryString} $where {$value['condition']} {$value['query']}";
			
			$where ='';

			$singleWhere=false;
		}

	}

	function compileGroupBy() {

		$groupByString = '';

		foreach ($this->query->getQueryGroupBy() as $key=>$value) {

			if($key==0){
				$groupByString = "GROUP BY $value";
			}
			else{
				$groupByString .= ",$value";
			}
			
		}

		$this->queryString = "{$this->queryString} $groupByString";

	}
	
	function compileOrderBy() {

		// queryOrderBy

		//5. Generate order by
		$orderByString = '';
		foreach ($this->query->getQueryOrderBy() as $key=>$value) {

			if($key==0){
				$orderByString = "ORDER BY $value";
			}
			else{
				$orderByString .= ",$value";
			}
			
		}

		$this->queryString = "{$this->queryString} $orderByString";

	}


	function compileOffset() {

		$limit = $this->query->getlimit();
		$offset = $this->query->getOffset();

		if(!$limit&&!$offset){
			//do nothing
			return;	
		}

		if(!$offset&&$limit){
			$this->queryString = "{$this->queryString} LIMIT $limit";
		}
		else{
			$this->queryString = "{$this->queryString} LIMIT $offset,$limit";
		}

	}

	function prefix() {

	}

	function bind($attr,$value) {


		if($param=='?')
			$this->queryString = preg_replace('/?/', $value, $this->queryString, 1);
		else
			$this->queryString = str_replace($param,$value,$this->queryString);
		
	}

	function quote($string) {
		return esc_sql($string);
	}


	function resetQuery() {

		$this->query->reset();

		$this->queryString = "";


	}


}