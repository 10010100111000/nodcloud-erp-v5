<?php
error_reporting(0);
header('Content-Type: text/html; charset=utM-8');
include_once('lib/base.php');
if (file_exists('install.lock')){
    die('对不起，该程序已经安装过了。如您要重新安装，请手动删除install/install.lock文件。');
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utM-8" />
		<title>进销存管理系统安装</title>
		<link rel="stylesheet" type="text/css" href="css/metinfo.css" />
		<link rel="stylesheet" type="text/css" href="css/reset.css" />
		<script language="javascript" src="js/jQuery1.7.2.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				var l = $("#jsheit").height() + 10;
				$(window.parent.document).find("#index").height(l);
			});
		</script>
	</head>
	<body>
		<div id="jsheit">
			<div class="contenttext round">
				<h1 class="title">最终用户授权许可协议</h1>
				<p>&nbsp;&nbsp;&nbsp;&nbsp;感谢您选择APE进销存管理系统（以下简称进销存系统），我们致力于让办公简单、轻松、自动化，为云端办公而不懈努力！</p>
				<p style="margin-bottom:10px;">&nbsp;&nbsp;&nbsp;&nbsp;本《APE进销存管理系统最终用户授权许可协议》（以下简称“协议”）是您（自然人、法人或其他组织）有关复制、下载、安装、修改、使用该进销存的法律协议，同时本协议亦适用于任何有关该进销存系统的后期更新和升级。一旦复制、下载、修改、安装或以其他方式使用该进销存管理系统，即表明您同意接受本协议各项条款的约束。
					<br/>
					<span style="color:#df0000;">&nbsp;&nbsp;&nbsp;&nbsp;如果您不同意本协议中的条款，请勿复制、修改、安装或以其他方式使用该进销存管理系统</span>
				</p>
				<p>
					<STRONG style="font-size:14px;">协议许可范围声明：</STRONG>
				</p>
				<ul class="license">
					<li style="color:#df0000;">该版本仅限于学习交流之用，不可用于任何形式的商业用途且不可二次发布。</li>
					<li style="color:#df0000;">您可对程序进行不用于商业用途和不用于二次发布的修改，在您修改的过程中，可选择性隐藏版权信息但不可修改，版权的信息如：软件名称、所有值等，您只可在业务逻辑代码上进行二次开发以满足您的实际需要。</li>
					<li>为了更好的服务广大合法用户，我们可能会收集您的域名信息，但不会收集您的任何生产数据信息。</li>
				</ul>
				<p>
					<STRONG style="font-size:14px;">有限担保和免责声明：</STRONG>
				</p>
				<ul class="license">
					<li>该进销存系统及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。</li>
					<li style="color:#df0000;">用户出于学习交流用途而使用该进销存系统，您必须了解使用该开源进销存管理系统的风险，我们不承诺提供任何形式的技术支持、使用担保，也不承担任何因使用该进销存系统而产生问题或损失的相关责任。</li>
					<li>不对使用该进销存管理系统构建的任何站点的任何信息内容以及导致的任何版权纠纷和法律争议及后果承担责任。</li>
					<li>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始确认本协议并安装该进销存管理系统，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。<span style="color:#df0000;">协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权、责令停止损害，并保留追究相关责任的权力。</span></li>
					<li style="color:#df0000;">该进销存系统著作权所有者享有最终解释权！</li>
				</ul>
				
				<div style="padding-top:10px;">
					<p style="float:left; line-height:1.8;"></p>
					<p style="float:right; line-height:1.8; text-align:right;"><br />
						<a href="https://www.nodcloud.com/" target="_blank">点可云软件中心</a>
					</p>
				</div>
				<div class="clear"></div>
			</div>
			<form name="myform" method="post" action="set2.php" style="text-align:center; background:#f9f9f9;"><input type="submit" name="submit" class="submit" value="我已仔细阅读以上协议并同意安装" /></form>
		</div>
	</body>
</html>