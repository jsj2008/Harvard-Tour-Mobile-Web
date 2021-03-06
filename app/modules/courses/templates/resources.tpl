{include file="findInclude:common/templates/header.tpl"}

{block name="resourcesHeader"}{/block}
{if $resourcesLinks}
{block name="groupSelector"}
{if $resourcesGroupLinks}
<ul class="tabstrip {$resourcesTabCount}tabs" id="{$tabstripId}-tabstrip">
{foreach $resourcesGroupLinks as $index => $groupLink}
<li{if $resourcesGroup == $index} class="active"{/if}><a href="{$groupLink.url}" onclick="updateGroupTab(this, '{$tabstripId}', '{$groupLink.url}'); return false;">{$groupLink.title}</a></li>
{/foreach}
</ul>
{/if}
{/block}
<div id="{$tabstripId}-content">
{block name="resourcesList"}
{foreach $resourcesLinks as $group}
    {if $group['url']}
    <div class="seeall"><a href="{$group['url']}">{'SEE_ALL'|getLocalizedString:$group['count']}</a></div>
    {/if}
    {$resourcesListHeading=$group.title|default:''}
    {include file="findInclude:modules/courses/templates/include/resourcesList.tpl" resourcesListHeading=$resourcesListHeading resources=$group.items}
{/foreach}
{/block}
</div>
{else}
<p>{"NO_RESOURCES"|getLocalizedString}</p>
{/if}
{block name="resourcesFooter"}{/block}

{include file="findInclude:common/templates/footer.tpl"}
