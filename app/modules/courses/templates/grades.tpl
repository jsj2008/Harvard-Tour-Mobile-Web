{include file="findInclude:common/templates/header.tpl"}

{block name="gradesHeader"}{/block}
{block name="gradesList"}
{if $gradesLinks}
{include file="findInclude:modules/courses/templates/include/gradesList.tpl" grades=$gradesLinks}
{else}
{"NO_GRADES"|getLocalizedString}
{/if}
{/block}
{block name="gradesFooter"}{/block}

{include file="findInclude:common/templates/footer.tpl"}
