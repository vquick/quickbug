<?php
/**
 * BUG相于配置
 */
return array(
	// BUG 严重程度
	'severity'=>array(
		1=>L('bug.suggest'),//'建议',
		2=>L('bug.notice'),//'提示',
		3=>L('bug.general'),//'一般',
		4=>L('bug.major'),//'严重',
		5=>L('bug.fatal'),//'致命',
	),

	// BUG 重现规律
	'frequency'=>array(
		1=>L('bug.always'),//'必然重现',
		2=>L('bug.sometimes'),//'很难重现',
		3=>L('bug.randam'),//'随机重现',
	),

	// BUG 缺陷类型
	'bugtype'=>array(
		1=>L('bug.functional'),//'功能问题',
		2=>L('bug.standard'),//'规范问题',
		3=>L('bug.performance'),//'性能问题',
		4=>L('bug.secure'),//'安全问题',
		5=>L('bug.UI'),//'界面问题',
	),
	
	// BUG状态
	'status'=>array( 
		1=>L('bug.new'),//'新', 
		2=>L('bug.accepted'),//'已接受',
		3=>L('bug.denied'),//'已拒绝',
		4=>L('bug.delay'),//'延后挂起',
		5=>L('bug.closed'),//'已关闭', 
		6=>L('bug.fixed'),//'已解决',
		7=>L('bug.reopened'),//'重新打开',
	),
	// BUG优先级
	'priority'=>array(
		1=>L('bug.none'),//'无关紧要',
		2=>L('bug.low'),//'低',
		3=>L('bug.normal'),//'中',
		4=>L('bug.high'),//'高',
		5=>L('bug.urgent'),//'紧急',
	),
		
	// BUG列表可用字段
	'listfields'=>array(
		'projectid'=>L('bug.project'),//'项目',
		'verid'=>L('bug.ver'),//'版本',
		'moduleid'=>L('bug.module'),//'模块',
		'severity'=>L('bug.severity'),//'严重程度',
		'frequency'=>L('bug.frequency'),//'重现规律',
		'priority'=>L('bug.priority'),//'优先级',
		'bugtype'=>L('bug.bugtype'),//'缺陷类型',
		'status'=>L('bug.status'),//'状态',
		'createuid'=>L('bug.creater'),//'创建人',
		'dateline'=>L('bug.create_time'),//'创建时间',
		'touserid'=>L('bug.to_user'),//'处理人',
		'lastuptime'=>L('bug.last_modify_time'),//'最后修改',
	),
);

