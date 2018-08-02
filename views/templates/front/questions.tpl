{extends file='layouts/layout-left-column.tpl'}

{block name="left_column"}
    <div id="left-column" class="col-xs-12 col-sm-4 col-md-3 card card-block">
        <div class="categories-list-header h2">{$categories_block.caption}</div>
        <ul class="categories-list">
            {foreach from=$categories_block.categories item=category}
                <li>
                    <a class="question-category-link" data-id="{$category.id_category}" href="#category{$category.id_category}">{$category.name}</a>
                </li>
            {/foreach}
        </ul>
    </div>
{/block}

{block name='content_wrapper'}
    {block name='content'}
        <div id="content" class="col-xs-12 col-sm-8 col-md-9">
            {if $questions|count}
                {foreach from=$questions item="question"}
                    <div class="category-question-card card card-block" data-ids="{$question.categories_ids}">
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
{/block}