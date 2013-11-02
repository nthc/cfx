<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<META http-equiv="Default-Style" content="main">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
{$styles}
{$scripts}
<title>{$title}</title>
</head>
<body onload="wyf.init()">
<div id="wrapper">
<div id="header">
	<div id="logo-image">
        <a href="{$prefix}/dashboard"><img src="{$prefix}/app/themes/default/images/logo_small.png" /></a>
    </div>
	<div id="user-info">
        <div id="user-info-text"><span class='icon iusers'>{$username}</span>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='/system/my_trail'><span class='icon itrail'>My Trail</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='{$prefix}/system/logout'><span class='icon ilogout'>Logout</span></a></div>
    </div>
	<div style="clear:both"></div>
</div>
</div>
<div id="top-menu">
<a href='{$prefix}/'><span class='icon ihome'>Home</span></a> {$top_menu}
</div>
{if $side_menu_hidden eq false}
    <div id="side-menu">
    {$side_menu}
    </div>
    {/if}

<div id="body" {if $side_menu_hidden eq true} style="width:100%" {/if}>
<div id="body-top">
<h2>{$module_name}</h2>
{$module_description}
</div>
{$notification}
<div id="body-internal">
{$content}
</div>
</div>
<div id="footer">
    <p>Powered by WYF Framework</p>
</div>
</div>
</body>
</html>

