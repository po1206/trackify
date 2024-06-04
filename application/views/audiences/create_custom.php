<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.js"></script>
<link href="<?php echo base_url(); ?>assets/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.css" rel="stylesheet" />
<div id="content">
    <div id="create_audience_page">
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <div class="fb-side-box">
            <div class="clearfix fb-title-wrap">
                <h3>Create a Custom Audience</h3>
                <a href="<?php echo base_url() . "MY_Facebook/custom_audiences"; ?>" class="btn btn-primary">FB Audiences</a>
            </div>
            <div class="box">
                <div class="box-content ca_form">
                    <?php echo form_open('Audiences/create', array('id' => 'sform')); ?>
                        <div class="form-group">
                            <label class="field_title">Ad Account</label>
                            <small>Please select an ad account.</small>
                            <select id="ad_accounts" name="ad_account" class="form-control select2" required>
                                <?php foreach ($ad_accounts as $a) : ?>
                                    <?php if ($ad_account_id == $a['id']) : ?>
                                        <?php echo "<option value='{$a['id']}' selected>{$a['name']}, ({$a['account_id']})</option>"; ?>
                                    <?php else: ?>
                                        <?php echo "<option value='{$a['id']}'>{$a['name']}, ({$a['account_id']})</option>"; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" id="pixels_wrap">
                        </div>

                        <div class="form-group">
                            <label class="field_title">CA Name</label>
                            <input type="text" required class="form-control" name="custom_audience_name"/>
                        </div>
                        
                        <div class="form-group">
                            <label class="field_title">Include</label>
                            <small>Traffic that meets the following condition:</small>
                            <div class="filter_group" role="group" aria-label="Include">
                                <div class="rule_type">
                                    <div class="btn-group">
                                        <div class="ck-button">
                                            <label><input type="radio" name="rule_type" value="and" checked><span>All</span></label>
                                        </div>
                                        <div class="ck-button eventtype">
                                            <label><input type="radio" name="rule_type" value="or"><span>Any</span></label>
                                        </div>
                                    </div>
                                    <span class="description"> of following rules should match</span>
                                </div>
                                <div class="filters" id="filters">
                                    <div class="filter_wrap">
                                        <div class="col-1 col-filtertype">
                                            <div class="select-editable">
                                                <select name="filtertype_dumb[]" class="form-control filtertype_dumb" required>
                                                    <option value="url" data-name="URL">URL</option>
                                                    <option value="content_ids" data-name="content_ids">Event content_ids (Product)</option>
                                                    <option value="content_name" data-name="content_name">Event content_name (Niche)</option>
                                                    <option value="content_timedata" data-name="content_timedata">Event content_timedata</option>
                                                    <option value="">Custom parameter</option>
                                                </select>
                                                <input class="form-control filtertype" name="filtertype[]" type="text" value="url" placeholder="">
                                            </div>
                                        </div>
                                        <div class="col-1 col-compare">
                                            <select class="form-control compare" name="compare[]" required>
                                                <option value="i_contains">Contains</option>
                                                <option value="i_not_contains">Not contains</option>
                                                <option value="eq">Equal</option>
                                                <option value="neq">Not equal</option>
                                                <option value="lt">Less than</option>
                                                <option value="lte">Less than or equal</option>
                                                <option value="gt">Greater than</option>
                                                <option value="gte">Greater than or equal</option>
                                                <!--<option value="in">In (text)</option>-->
                                            </select>
                                        </div>
                                        <div class="col-1 col-filtervalue">
                                            <input type="text" class="form-control filtervalue" name="filtervalue[]" placeholder="Add values" required />
                                            <button type="button" class="close2" style="display: none;">×</button>
                                        </div>
                                    </div>
                                </div>
                                <a href="javascript:;" id="add_rule_link">Add rule</a>
                            </div>
                            
                        </div>

                        <div class="form-group clearfix" id="event_type_wrap">                       
                            <label class="field_title">Event Type</label>
                            <div class="ck-button eventtype">
                                <label><input type="checkbox" name="eventtype[]" value="ViewContent"><span>ViewContent</span></label>
                            </div>
                            <div class="ck-button eventtype">
                                <label><input type="checkbox" name="eventtype[]" value="AddToCart"><span>AddToCart</span></label>
                            </div>
                            <div class="ck-button eventtype">
                                <label><input type="checkbox" name="eventtype[]" value="InitiateCheckout"><span>InitiateCheckout</span></label>
                            </div>
                            <div class="ck-button eventtype">
                                <label><input type="checkbox" name="eventtype[]" value="AddPaymentInfo"><span>AddPaymentInfo</span></label>
                            </div>
                            <div class="ck-button eventtype">
                                <label><input type="checkbox" name="eventtype[]" value="Purchase"><span>Purchase</span></label>
                            </div>
                        </div>

                        <div class="form-group clearfix">
                            <label class="field_title">Lookback Period</label>
                            <small>Select the amount of days the data will stay in the audience. You need to select at least one retention time.</small>
                            <?php foreach ($lookback as $l) : ?>
                                <div class="ck-button custom_look_back_days">
                                    <label><input type="checkbox" name="custom_look_back_days[]" value="<?php echo $l; ?>"><span><?php echo $l; ?></span></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group clearfix inline_field" id="device_type_wrap" style="display: none;">                       
                            <label class="field_title">Visited Website On <a class="fb-tooltip" href="#" data-placement="right" title="The type of the device where the pixel is triggered"><i class="fa fa-question-circle"></i></a></label>
                            <div class="field_control">
                                <div class="ck-button">
                                    <label><input type="radio" name="device_type" value="all" checked="checked"><span>All</span></label>
                                </div>
                                <div class="ck-button">
                                    <label><input type="radio" name="device_type" value="mobile"><span>Mobile</span></label>
                                </div>
                                <div class="ck-button">
                                    <label><input type="radio" name="device_type" value="desktop"><span>Desktop</span></label>
                                </div>
                            </div>
                        </div>                  

                        <div class="form-group">
                           <input name="submit" type="submit" value="Create" class="btn btn-primary" id="submit"/>
                        </div>
                    <?php echo form_close(); ?>                
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {        

        $("#sform").submit(function(e) {
            checked = $("input[name='eventtype[]']:checked").length;
            if (!checked && $("#filtertype_1").val() != "url") {
                alert("You must select at least one event type.");
                return false;
            }
            
            checked = $("input[name='custom_look_back_days[]']:checked").length;
            if (!checked) {
                alert("You must select at least one retention time.");
                return false;
            }

            if ($("#pixel_id").length == 0) {
                alert("You have no pixel selected.");
                return false;   
            }

            return true;
        });

        $(".filtertype").change(function(e) {
            if ($(this).val() == "url") {
                $("#event_type_wrap").hide();
            } else {
                $("#event_type_wrap").show();
            }
        });

        $("#ad_accounts").change(function(e) {
            fb_block_selector(".ca_form");
            $.ajax({
                url: base_url + 'MY_Facebook/get_pixels_by_adaccount/' + $(this).val(),
                type: 'get',
                success: function(response) {
                    console.log(response);
                    if (response.length > 0) {
                        html = '<label class="field_title">Pixel ID</label> \
                                    <select id="pixel_id" name="pixel_id" class="form-control select2" required>';
                        for (i = 0; i < response.length; i++) {
                            html += '<option value="' + response[i].id + '">' + response[i].name + ', (' + response[i].id + ')</option>';
                        }
                        html += '</select>';
                    } else {
                        html = '<div class="alert alert-warning"> \
                                    <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button> \
                                    <strong>You have no pixel for this ad account.</strong> \
                                </div>';
                    }
                    $("#pixels_wrap").html(html);
                    fb_unblock_selector(".ca_form");
                },
                error: function(response) {
                    alert("error");
                }
            });
        });

        $("#ad_accounts").trigger("change");

        var filter_html = '<div class="filter_wrap"> \
                            <div class="col-1 col-filtertype"> \
                                <div class="select-editable"> \
                                    <select name="filtertype_dumb[]" class="form-control filtertype_dumb" required> \
                                        <option value="url" data-name="URL">URL</option> \
                                        <option value="content_ids" data-name="content_ids">Event content_ids (Product)</option> \
                                        <option value="content_name" data-name="content_name">Event content_name (Niche)</option> \
                                        <option value="content_timedata" data-name="content_timedata">Event content_timedata</option> \
                                        <option value="">Custom parameter</option> \
                                    </select> \
                                    <input class="form-control filtertype" name="filtertype[]" type="text" value="url" placeholder=""> \
                                </div> \
                            </div> \
                            <div class="col-1 col-compare"> \
                                <select class="form-control compare" name="compare[]" required> \
                                    <option value="i_contains">Contains</option> \
                                    <option value="i_not_contains">Not contains</option> \
                                    <option value="eq">Equal</option> \
                                    <option value="neq">Not equal</option> \
                                    <option value="lt">Less than</option> \
                                    <option value="lte">Less than or equal</option> \
                                    <option value="gt">Greater than</option> \
                                    <option value="gte">Greater than or equal</option> \
                                    <!--<option value="in">In (text)</option>--> \
                                </select> \
                            </div> \
                            <div class="col-1 col-filtervalue"> \
                                <input type="text" class="form-control filtervalue" name="filtervalue[]" placeholder="Add values" required/> \
                                <button type="button" class="close2">×</button> \
                            </div> \
                           </div>';
        $("#add_rule_link").click(function(e) {
            $("#filters").append(filter_html);
            $('.filtervalue').tokenfield({ createTokensOnBlur : true});
        });

        $(document).on("click", ".filter_wrap .close2", function(e) {
            $(this).parents(".filter_wrap").remove();
        });

        $(document).on("change", ".filter_wrap .filtertype_dumb", function(e) {
            $(this).next(".filtertype").val($(this).val());
        });

        $('.filtervalue').tokenfield({ createTokensOnBlur : true});
    });

</script>
