{extends file='layouts/layout-content-only.tpl'}

{block name='content'}
    <header class="page-header">
        <h1>{$caption}</h1>
    </header>
    <div id="content" class="page-cms">
    {if $questions|count}
        {foreach from=$questions item="question"}
            <div class="category-question-card card card-block">
                <p class="question-question h3">{$question.question}</p>
                <p class="question-categories small">{$categories_block.caption}: {$question.categories_names}</p>
                <p class="question-answer">{$question.answer nofilter}</p>
            </div>
        {/foreach}
    {else}
        <div class="card card-block">
            {$empty_text}
        </div>
    {/if}
    </div>
{/block}