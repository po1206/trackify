<div id="content">
    <div id="feeds_page">
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <div class="validator-error alert alert-warning" style="display: none;">
            <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
            <strong><span class="feedstatus"></span></strong>
        </div>
        <div class="fb_row feed_builder_wrap">
            <div class="row-fluid" style="text-align: center; margin-bottom: 10px;">
                <div id="upDateStoreName">
                    <label class="edit_domain_label">Edit Store Domain for Product Links: 
                        <select name="default_domain_protocol" id="default_domain_protocol" style="display: none;">
                            <option value="http://">http://</option>
                            <option value="https://">https://</option>
                        </select>
                        <input type="text" name="default_domain" class="input_default_domain" id="default_domain" value="<?php echo get_store_name_from_channels_set($_SESSION["shop"]); ?>" style="" readonly />
                        <a href="#" class="btn domain_edit_btn" id="domainEdit">Edit</a> 
                        <a href="#" class="btn domain_edit_btn" id="domainReset">Reset</a>
                    </label>
                </div>
            </div>
            <div id="tableResults" class="row-fluid">
                <table class="table table-striped table-condensed" id="fb_table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Catalog Feed for Facebook</th>
                            <th>Products Count</th>
                            <th>Last Changes</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($channels)) : ?>
                            <?php foreach ($channels as $row) : ?>
                            <tr>
                                <td class="feedidRenderTd" data-id="<?php echo $row["id"]; ?>"><?php echo $row["id"]; ?></td>
                                <?php if ($row["feedurl_changed"] == 1): ?>
                                    <td><a href="<?php echo base_url() . "feeds/" . bin2hex(base64_encode($row["id"])); ?>" target="_blank"><?php echo base_url() . "feeds/" . bin2hex(base64_encode($row["id"])); ?></a></td>
                                <?php else: ?>
                                    <?php if (!empty($row["feed_file"])) : ?>
                                    <td><a href="<?php echo $row["feed_file"]; ?>" target="_blank"><?php echo $row["feed_file"]; ?></a></td>
                                    <?php else : ?>
                                        <td>You have no feed created yet. Please click on Rebuild button to build a feed.</td>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <td><?php echo $row["product_count"];?></td>
                                <td style="white-space: nowrap;"><?php echo date('d-m-Y', strtotime($row["datetime"])); ?></td>
                                <td style="white-space: nowrap;"><span class="feedstatus">-</span></td>
                                <td class="action">
                                    <a href="javascript:void(0)" class="btn btn-primary reBuildFeed" id="act-<?php echo $row["id"]; ?>" style="margin-bottom: 6px;">Rebuild Catalog</a>
                                    <a href="<?php echo base_url() . "edit-feed/" . $row["id"] . "/"; ?>" class="btn btn-primary" style="margin-bottom: 6px;">Edit Feed</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <script>
                    table = $("#fb_table").DataTable({
                                "bLengthChange": false,
                                "searching": false,
                                "pageLength": 10,
                                "aoColumnDefs": [
                                    { 'bSortable': false, 'aTargets': [0,1,2,3,4,5] }
                                ]
                    });

                    function reBuildFeed(channel_id){
                        fb_block("Please don\'t reload this page");
                        
                        $.ajax({
                            //url: "<?php echo base_url() . "update_feeds/" . $_SESSION["shop"]; ?>",
                            url: "<?php echo "https://process.redretarget.com/sapp/update_feeds/" . $_SESSION["shop"]; ?>",
                            dataType: "json",
                            success: function( data ) {
                                fb_unblock();
                                location.reload();    
                            }
                        });
                    }

                    function changeDefaultDomain(channel_id){
                        var jqxhr = $.post( "<?php echo base_url() . "Catalog/update_store_domain/"; ?>",{ default_store_name: $("#default_domain").val(), default_domain_protocol : $(default_domain_protocol).val() }, "json")
                          .done(function(data) {
                            var response = $.parseJSON(data);
                            console.log(response);
                            $("#default_domain").val(response.default_store_name);
                            //$("#default_domain").val()
                          })
                          .fail(function() {
                            
                          })
                          .always(function() {
                              
                              $("#default_domain").attr("readonly","readonly");
                              $("#default_domain").addClass("read_only")
                              ;
                              $("#default_domain_protocol").hide();
                              $("#domainEdit").html("Edit");
                              $("#domainReset").html("Reset");
                              $("#domainReset").removeAttr("disabled");
                              $("#domainEdit").removeAttr("disabled");
                              /* trigger update */
                              $(".feedidINPUT").each(function(){
                                  var feedidin = $(this).val();
                                  if(feedidin != null ) {
                                      //$(".iframetemplate").clone(true).css("display","block").prop('src', 'datamap.php?feedid='+feedidin).appendTo( "#iframeContainerProg");
                                      reBuildFeed(channel_id);
                                  }
                              });
                          });
                    }
                    
                </script>
            </div>
        </div>

        <div class="page_tool_bar clearfix">
            <div class="form-group">
            <?php echo form_open('MY_Facebook/redirect/create-catalog', array('id' => 'sform')); ?>
                <label class="label-2">Select a Business Account: </label>
                <select id="business_accounts" name="object_id" class="form-control select2 inline-select" required>
                    <?php
                        foreach ($businesses as $a) {
                            if ($business_id == $a['id']) {
                                echo "<option value='{$a['id']}' selected>{$a['name']}</option>";
                            } else {
                                echo "<option value='{$a['id']}'>{$a['name']}</option>";
                            }
                        }
                    ?>
                </select>
                <button type="button" id="facebook_catalog_list_btn" class="btn btn-primary" style="margin-left: 10px;">Facebook Catalog List</button>
                <button type="submit" name="submit" class="btn btn-primary" id="create_product_catalog_btn">+ Add Product Catalog</button>
            <?php echo form_close(); ?>
            </div>
        </div>
        
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        
        var channel_id = <?php echo $channels[0]["id"]; ?>;
        
        //Check Feed Status
        $.getJSON("<?php echo base_url() . "Catalog/check_category_map/"; ?>" + channel_id, function( data ) {
            $("span.feedstatus").html(data.message);
            if (data.result == false ) {
                $(".validator-error").show();
            }
        });
        
        $("#domainReset").on('click',function(){
                var defaultdomain = '<?php echo $shop['domain']; ?>';
                $("#default_domain").val(defaultdomain);
                $("#domainReset").html("Please wait...");
                $("#domainReset").attr("disabled", "disabled");
                $("#default_domain").attr("readonly","readonly");
                $("#default_domain").addClass("read_only");
                $("#default_domain_protocol").hide();
                changeDefaultDomain(channel_id);
        });

        $("#domainEdit").on('click',function(){
             if($(this).hasClass("UpdateDefaultDomain")){
                $("#domainEdit").html("Please wait...");
                $("#domainEdit").attr("disabled", "disabled");
                $("#default_domain").attr("readonly","readonly");
                $("#default_domain").addClass("read_only");
                $("#default_domain_protocol").hide();
                changeDefaultDomain();
                $(this).removeClass("UpdateDefaultDomain");
             }else{
                $("#default_domain").removeAttr("readonly");
                $("#default_domain").removeClass("read_only");
                $("#default_domain_protocol").show();
                $(this).html("Update");
                $(this).addClass("UpdateDefaultDomain");
             }
        });

        $(".reBuildFeed").on('click',function() {
            reBuildFeed(channel_id);       
        });

        $("#facebook_catalog_list_btn").on("click", function() {
             location.href = base_url + "facebook-feeds/" + $("#business_accounts").val();
        });

        
   });
</script>
