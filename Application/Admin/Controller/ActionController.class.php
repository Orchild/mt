<?php

namespace Admin\Controller;

use Common\Controller\ExtendController;
use Common\Util\Jpush;

class ActionController extends ExtendController {
	protected $model;
	public function _initialize() {
		parent::_initialize ();
		$this->model = D ( 'Action' );
	}
	/**
	 * 主页面
	 */
	public function index() {
		$this->display ();
	}
	/**
	 * 数据列表
	 */
	public function lists() {
		if (IS_POST) {
			$title = PUT ( 'title' );
			$map = array ();
			$map ['title'] = array (
					'like',
					"%$title%" 
			);
			parent::page ( array (
					'size' => PUT ( 'size', 10 ),
					'page' => PUT ( 'p' ),
					'where' => $map,
					'orderby' => 'id desc' 
			) );
		} else {
			$this->display ();
		}
	}
	/**
	 * 添加数据
	 */
	public function add() {
		if (IS_POST) {
			$result = $this->__validate ( PUT () );
			if (! $result ['success'])
				$this->error ( $result ['msg'] );
			elseif ($this->__create ())
				$this->success ( '添加成功' );
			else
				$this->error ( '添加失败' );
		} else {
			$this->display ( 'edit' );
		}
	}
	/**
	 * 编辑数据
	 */
	public function edit() {
		if (IS_POST) {
			$result = $this->__validate ( PUT () );
			if (! $result ['success'])
				$this->error ( $result ['msg'] );
			elseif ($this->__update ())
				$this->success ( '更新成功' );
			else
				$this->error ( '更新失败' );
		} elseif (IS_GET && PUT ( 'get.id' )) {
			$res = $this->__find ( PUT ( 'get.id' ) );
			$this->ajaxReturn ( $res );
		} else {
			$this->display ();
		}
	}
	/**
	 * 修改排序
	 */
	public function sort() {
		if (IS_POST) {
			$id = PUT ( 'id' );
			if ($id > 0) {
				if ($this->__sort ( $id, PUT ( 'value' ), PUT ( 'field' ) ))
					$this->success ( '修改排序成功' );
				else
					$this->error ( '修改排序失败' );
			} else {
				$this->__init_sort ();
				$this->success ( '修改排序失败' );
			}
		}
	}
	/**
	 * 修改状态
	 */
	public function status() {
		if (IS_POST) {
			$id = PUT ( 'id' );
			$value = PUT ( 'value' );
			if ($value == 2) {
				$row = $this->__find ( $id );
				$msg = array ();
				$msg ['url'] = URI_ROUTE ( 'message' . $row ['id'], true, true );
				$msg ['remark'] = $row ['remark'];
				$msg ['content'] = $row ['title'];
				if ($row ['msg_cat'] == 0) {
					Jpush::pushAll ( $msg );
				} else {
					$audience = array (
							'cat' . $row ['msg_cat'] 
					);
					$res ['android'] = Jpush::tag ( $msg, $audience, 0, 'android' );
					$res ['ios'] = Jpush::tag ( $msg, $audience, 1, 'ios' );
				}
			}
			if ($this->__status ( PUT ( 'id' ), PUT ( 'value' ), PUT ( 'field' ) )) {
				if ($value == 2) {
					$this->success ( '推送成功！' );
				} else {
					$this->success ( '状态修改成功' );
				}
			} else
				$this->error ( '状态修改失败' );
		}
	}
	/**
	 * 删除数据
	 */
	public function delete() {
		if (IS_POST) {
			$map = array ();
			$map ['id'] = PUT ( 'id' );
			$result = $this->__delete ( $map, false, 'image' );
			if ($result ['success']) {
				$this->success ( '删除成功' );
			} else
				$this->error ( '删除失败' );
		}
	}
	/**
	 * 消息类别
	 */
	public function category() {
		$result = array ();
		$model = D ( 'Category' );
		$map ['status'] = true;
		$result ['items'] = $model->where ( $map )->field ( array (
				'id' => 'key',
				'title' => 'val' 
		) )->order ( 'sort desc,updated desc' )->select ();
		$cat_none = array (
				'key' => 0,
				'val' => '无分类' 
		);
		$result ['items'] [sizeof ( $result ['items'] )] = $cat_none;
		// wlog('$result[items]', $result['items']);
		$this->ajaxReturn ( $result );
	}
	/**
	 * 上传图片
	 */
	public function upload() {
		$this->uploadImage ( array (
				'thumb' => array (
						100,
						100 
				) 
		) );
	}
	/**
	 * 上传图片
	 */
	public function hotupload() {
		$this->uploadImage ( array (
				'path' => 'headmsg',
				'thumb' => array (
						800,
						400 
				) 
		) );
	}
}