{section name=stylesheet loop=$stylesheets}
<link type="text/css" rel="stylesheet" title="{$title}" href="{$prefix}/{$stylesheets[stylesheet].href}" media="{$stylesheets[stylesheet].media}"  />
{/section}
