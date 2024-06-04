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
                    <?php echo form_open('MY_Facebook/create_audience/custom', array('id' => 'sform')); ?>
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

                        <div class="form-group">
                            <label class="field_title">CA Name</label>
                            <input type="text" required class="form-control" name="custom_audience_name"/>
                        </div>
                        
                        <div class="form-group">
                            <label class="field_title">Include</label>
                            <small>Traffic that meets the following condition:</small>
                            <div class="filter_group" role="group" aria-label="Include">
                                <div class="filter_wrap">
                                    <div class="col-1 col-filtertype">
                                        <select class="form-control filtertype" name="filtertype" id="filtertype_1">
                                            <option value="content_ids" data-name="content_ids">Event content_ids (Product)</option>
                                            <option value="content_name" data-name="content_name">Event content_name (Niche)</option>
                                            <option value="content_timedata" data-name="content_timedata">Event content_timedata</option>
                                            <option value="url" data-name="URL">URL</option>
                                        </select>
                                    </div>
                                    <div class="col-1 col-compare">
                                        <select class="form-control compare" name="compare">
                                            <option value="i_contains">Contains</option>
                                        </select>
                                    </div>
                                    <div class="col-1 col-filtervalue">
                                        <input type="text" class="form-control filtervalue" name="filtervalue" placeholder="Add a value" required/>
                                    </div>
                                </div>
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
        });

        $(".filtertype").change(function(e) {
            if ($(this).val() == "url") {
                $("#event_type_wrap").hide();
            } else {
                $("#event_type_wrap").show();
            }
        });
    });

</script>
