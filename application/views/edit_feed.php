<?php
    $default_google_cat=array();
    $taxonomy = "";
    $default_google_cat_id = intval($default_google_prod_cat[0]['default_google_prod_tax_id']);
    $default_google_cat = get_google_prod_taxonomy($default_google_cat_id);
    
    //Auto detect Default category
    $assgin_default_google_cat ="";
    $assgin_default_google_cat_id ="";

?><div id="content">
    <div id="feeds_page">
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        
        <div class='validator-error error alert alert-danger' style="display: none;">
           <strong>Error: </strong>Please map all fields before saving. After edit click on save button for saving changes.
        </div>
        
        <?php echo form_open("save-feed/$id", array('id' => 'manage_feed_frm')); ?>
        <div class="fb_row">
            <div class="controls">
                <a class="btn pull-left" href="<?php echo base_url() . "build-feed"; ?>">&laquo; Previous Tab</a>
                <button type="button" class="btn btn-warning  pull-right" id="reset_manage_feeds">Reset Default</button>
                <button class="btn btn-primary pull-right" name="save" type="submit" id="save_btn_frm" style="margin-right: 10px;">Save</button>
            </div>
        </div>
        <div style="margin-top:10px;display: inline-block;width: 100%;"> 
            <table class="table table-bordered table-striped table-condensed ">
                <thead>
                    <tr>
                        <th>Assign Default Google Product Category</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select data-placeholder="Search for a category (Please enter minimum 3 characters)" class="chosen-select" tabindex="2" name="default_google_product_cat">
                                <?php if(!empty($default_google_cat)): ?>                                 
                                   <option selected="selected" value="<?php echo $default_google_cat['id']; ?>"><?php echo htmlspecialchars($default_google_cat['taxonomy']); ?></option>
                                <?php else: ?>
                                    <option selected="selected" value="<?php echo $assgin_default_google_cat_id; ?>"><?php echo htmlspecialchars($assgin_default_google_cat); ?></option>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="fb_row">
            <div id="tableResults" class="row-fluid">                
                <table class="table table-bordered table-striped table-condensed categoryFilterTable">
                    <thead>
                        <tr>
                            <th>Shopify Product Type</th>
                            <th>Assign Google Product Category</th>
                            <!-- <th style="width: 30%;">Default Google Product Category</th> -->
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)) : ?>
                        <?php foreach ($categories as $row) : ?>
                        <tr>
                            <td>
                                <input type="hidden" value="<?php echo $row["id"]; ?>" name="ids[]" class="ids"/>
                                <input class="form-control conditions" name="conditions[]" size="50" type="text" value="<?php echo $row['conditions']; ?>"/>
                            </td>
                            <td>
                                <select data-placeholder="Search for a category (Please enter minimum 3 characters)" class="chosen-select" tabindex="2" name="categories[]">
                                    <?php $catdetails = get_google_prod_taxonomy($row['category_id']); ?>  
                                    <?php if(!empty($catdetails['id'])):
                                            if(empty($assgin_default_google_cat_id)){
                                                $assgin_default_google_cat = $catdetails['taxonomy'];
                                                $assgin_default_google_cat_id = $catdetails['id'];
                                            }
                                     ?>
                                        <option selected="selected" value="<?php echo $catdetails['id']; ?>"><?php echo htmlspecialchars($catdetails['taxonomy']); ?></option>
                                    <?php else: ?>
                                        <?php if(!empty($default_google_cat)): ?>                                 
                                           <option selected="selected" value="<?php echo $default_google_cat['id']; ?>"><?php echo htmlspecialchars($default_google_cat['taxonomy']); ?></option>
                                        <?php else: ?>
                                            <option selected="selected" value=""> </option>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <!-- <td>
                                <?php $prodcat = get_prodcat($row['conditions']); ?>
                                <textarea class="form-control taxonomy-text" readonly="" /><?php echo $prodcat['taxonomy']; ?></textarea>
                            </td> -->
                            <td class="action">
                                <a class="btn btn-danger removeCategoryFilter"><i class="glyphicon glyphicon-remove"></i></a>                                
                                <a class="btn addnewCategoryFilter"><i class="glyphicon glyphicon-plus"></i></a>                                
                            </td>
                        </tr>     
                        <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td>
                                    <input type="hidden" value="-1" name="ids[]" class="ids"/>
                                    <input class="form-control conditions" name="conditions[]" size="50" type="text" value=""/>
                                </td>
                                <td>
                                    <select data-placeholder="Search for a category (Please enter minimum 3 characters)" class="chosen-select" tabindex="2" name="categories[]">                                       
                                        <option selected="selected" value=""> </option>
                                        
                                    </select>
                                </td>
                                <!-- <td>
                                    <?php $prodcat = get_prodcat($row['conditions']); ?>
                                    <textarea class="form-control taxonomy-text" readonly="" /><?php echo $prodcat['taxonomy']; ?></textarea>
                                </td> -->
                                <td class="action">
                                    <a class="btn btn-danger removeCategoryFilter"><i class="glyphicon glyphicon-remove"></i></a>                                
                                    <a class="btn addnewCategoryFilter"><i class="glyphicon glyphicon-plus"></i></a>                                
                                </td>
                            </tr>    
                        
                        <?php endif; ?>
                        
                    </tbody>
                </table>
                
            </div>
        </div>
        
        <?php echo form_close(); ?>
    </div>
</div>


<script>

    jQuery(document).ready(function($) {

    	var channel_id = "<?php echo $channel; ?>";

        $(".categoryFilterTable tr:last").find(".addnewCategoryFilter").css("display", "inline-block");
        
        $('.chosen-select').chosen({width: "95%", no_results_text: "Oops, nothing found!", allow_single_deselect: true});

        $(document).on('keydown.autocomplete', '.chosen-search input', function() {
            $(this).autocomplete({
                minLength: 3,
                select: function(event, ui) {
                    // here I need the input element that have triggered the event
                    //alert($(this).attr('name'));
                },
                source: function( request, response ) {
                    var currentElem = this.element;
                    $.ajax({
                        url: "<?php echo base_url() . "Catalog/search_cat/"; ?>" + request.term,
                        dataType: "json",
                        beforeSend: function(){ $(currentElem).closest('td').find('.chosen-select').empty();},
                        success: function( data ) {
                            response( $.map( data, function( item ) {
                                $(currentElem).closest('td').find('.chosen-select').append('<option value="' + item.id + '">' + item.taxonomy + '</option>');
                                $(currentElem).closest('td').find('.chosen-select').trigger("chosen:updated");
                            }));
                        }
                    });
                }
            });
        });

        //Clone Tr 
        var i = 1;

        $("body").on("click", ".removeCategoryFilter", function() {
            if ($(".categoryFilterTable tr").length <= 2) {
                //return false;
            }
            console.log($(".categoryFilterTable tr").length);
            $(this).closest('tr').remove();
            $(".categoryFilterTable tr:last").find(".addnewCategoryFilter").css("display", "inline-block");

        });

        $("body").on("click", ".addnewCategoryFilter", function() {
            $(this).closest('tr').clone().find("input,select,textarea").each(function() {
                $(this).attr({'value': ''});
                
                if ($(this).hasClass('chosen-select')) {                    
                    $(this).html('<option value=""></option>');
                    $(this).trigger("chosen:updated");                    
                } else if ($(this).hasClass("taxonomy-text")) {
                    $(this).html("");
                } else if ($(this).hasClass("ids")) {
                    $(this).val("-1");
                }
                
                $(this).closest('.chosen-container').remove();
                $(this).closest('.chosen-select').chosen({width: "95%", no_results_text: "Oops, nothing found!", allow_single_deselect: true});
                
            }).end().appendTo(".categoryFilterTable");

            $(this).hide();
            i++;
        });

        /* Validate */

        //validateForm();
        function validateForm() {
            var $selects = $('#manage_feed_frm').find('.chosen-select'), valid = true;
            if ($selects.length) {
                //validate selects
                $selects.each(function() {
                    if ($(this).val() == "") {
                        valid = false;
                        $(this).closest('tr').addClass("danger");
                    }
                });
            }
          
            //only submit the form if all fields have passed validatio            
            if (valid == true) {
                $(".validator-error").hide();
                $("tr.error").removeClass("danger");
                return true;
            } else {
                $(".validator-error").show();
                return false;
            }                          
        }
     
        $('#manage_feed_frm').on('submit', function() {
            //if (!validateForm()) {
               // return false;
            //}
        });


        function re_set_categories(channel_id){
	        fb_block("Please don\'t reload this page");
	        
	        $.ajax({
	            url: "<?php echo base_url() . "Catalog/re_set_categories/"; ?>" + channel_id,
	            dataType: "json",                
	            success: function( data ) {
	                console.log(data);
	                fb_unblock();
	                location.reload();    
	            }
	        });
	    }

        $("#reset_manage_feeds").on("click",function() {
            var r = confirm("Are you sure? This will restore category fields to default and rebuild the catalog.");
            if (r == true) {
               re_set_categories(channel_id);
            }
        });
    });
</script>
