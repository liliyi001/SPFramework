<?php

class index extends View{

	/**
	 * 测试控制器
	 *
	 * 详细说明
	 * @形参
	 * @访问      公有
	 * @返回值    void
	 * @throws
	 * helius
	 */
	function index()
	{
		/* output test */
		//$this->assign('test',\test_module\tools::get());

		/* mysql query test */
		$mysql = new Mysql('test2','member');
		$mysql->field(array('*'));
		//$mysql->where(array('id'=>array(4,5)));
		$res = $mysql->fetchAll();var_dump($res);die;

		/* mysql insert test */
		//$res = $mysql->insert(array('id'=>1,'tips_id'=>1,'a'=>1,'b'=>1));

		/* mysql update test */
		//$res = $mysql->update(array('id'=>1),arary('a'=>1));

		/* mysql delete test */
		//$res = $mysql->delete(array('id'=>1));

		/* redis test */
		//$redis = new Redish(0);
		//$redis->redis()->set('xx','xx');

		/* view test */
		//$this->toView();
	}

}

?>