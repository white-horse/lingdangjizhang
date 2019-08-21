<?php
/**
 * 账单管理控制器
 * @author Jerry
 * @date 20190821
 */
namespace app\wxapp\controller;

use app\wxapp\model\BillTag;

class Bill extends Base
{
    /**@var object 常用实体对象  */
    protected static $billTagEntity = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->checkUser();
        $this->init();
    }
    
    /**
     * 获取账单标签列表
     */
    public function getTags()
    {
        $where = ['is_show' => 1];
        $fields = [
//             'id AS tag_id',
            'tag_name AS title',
            'tag_color_name name',
            'tag_color_value AS color'
        ];
        $list = self::$billTagEntity->getTagList($where, $fields);
        
        return $this->outputData(200, 'success', $list);
    }
    
    
    /**
     * 初始化常用实体
     */
    protected function init()
    {
        self::$billTagEntity = new BillTag();
    }
}