{$tabBodies=array()}
{foreach $tabs as $key}
    {capture name=tab assign="tabBody"}
    <div id="{$key}-tabbody">
    {if $currentTab == $key}
        {include file="findInclude:modules/courses/templates/$key.tpl" ajaxContentLoad=true}
    {/if}
    </div>
    {/capture}

    {$tabBodies[$key] = $tabBody}
{/foreach}
{block name="tabs"}
<div id="tabscontainer" class="tabscount-{count($tabBodies)}">
{include file="findInclude:common/templates/tabs.tpl" tabBodies=$tabBodies smallTabs=true}
</div>
{/block}
