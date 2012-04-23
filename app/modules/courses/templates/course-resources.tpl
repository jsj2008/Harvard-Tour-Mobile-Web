{block name="groupSelector"}
<ul class="tabstrip {$resourcesTabCount}tabs">
{foreach $resourcesGroupLinks as $index => $groupLink}
<li{if $resourcesGroup == $index} class="active"{/if}><a href="{$groupLink.url}">By {$groupLink.title}</a>
{/foreach}
</ul>
{/block}
{foreach $resourcesLinks as $group}
    {if $group['url']}
    <div class="seeall"><a href="{$group['url']}">{'SEE_ALL'|getLocalizedString:$group['count']}</a></div>
    {/if}
    {$resourcesListHeading=$group.title|default:''}
    {include file="findInclude:modules/courses/templates/resourcesList.tpl" resourcesListHeading=$resourcesListHeading resources=$group.items}
{/foreach}
