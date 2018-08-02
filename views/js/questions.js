
(function(){
   $('.question-category-link').on('click', function(e){
       e.preventDefault();
       var link_category_id = $(this).data('id').toString();
       $('.category-question-card').each(function(){
           var block_category_ids = $(this).data('ids').toString().split(';');
           if (block_category_ids.indexOf(link_category_id) !== -1 || link_category_id === "0") {
               $(this).show();
           }
           else {
               $(this).hide();
           }
       })
   })
})()