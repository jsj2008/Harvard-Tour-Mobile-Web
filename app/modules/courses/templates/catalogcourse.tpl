{include file="findInclude:common/templates/header.tpl"}

{include file="findInclude:modules/courses/templates/include/coursedetailhead.tpl"}
{$tabBodies=array()}
{foreach $tabs as $key}
    {capture name="tab" assign="tabBody"}
    {include file="findInclude:modules/courses/templates/info/{$tabTypes[$key]}.tpl" tabInfoDetails=$tabDetails[$key]}
    {/capture}
    {$tabBodies[$key] = $tabBody}
{/foreach}
{block name="tabs"}
<div id="tabscontainer" class="tabscount-{count($tabBodies)}">
{include file="findInclude:common/templates/tabs.tpl" tabBodies=$tabBodies smallTabs=true}
</div>
{/block}

{include file="findInclude:common/templates/footer.tpl"}
