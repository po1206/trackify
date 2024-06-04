<div id="content">
    <div id="settings_page">
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <div class="fb-side-box box">
            <div class="box-content">                
                <?php echo form_open('settings', array('id' => 'sform')); ?>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">FB Ads Mastery Webinar (replay):</label>
                        <div class="col-md-7">
                            <a href="https://app.webinarjam.net/replay/18254/338ce212e3/3/0" target="_blank"><img src="<?php echo base_url(); ?>assets/images/watch-now.png" width=110></a>
                        </div>
                    </div>                    
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">FB Business:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop dumb"></div>
                            <div class="field">
                                <select id="business_accounts" name="business_account" class="form-control business_account" placeholder="Select your main FB business account">
                                    <?php
                                        if (!empty($businesses)) {
                                            foreach ($businesses as $a) {
                                                if ($settings["fb_business"] == $a['id']) {
                                                    echo "<option value='{$a['id']}' selected>{$a['name']}</option>";
                                                } else {
                                                    echo "<option value='{$a['id']}'>{$a['name']}</option>";
                                                }
                                            }
                                        } else {
                                            echo "<option value='' selected></option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">FB Account Pixel ID:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/settings-FBpixel.html" >?</a></div>
                            <div class="field">
                                <div class="select-editable">
                                    <select id="ad_accounts" name="ad_account" class="form-control ad_account">
                                        <option value=""></option>
                                        <?php foreach ($business_ad_accounts as $a) : ?>
                                            <?php if (!empty($a['pixel_id']) && $settings["ad_account"] == $a['adaccount']['id']) : ?>
                                                <?php 
                                                    if (!empty($a['pixel_id']))
                                                        echo "<option value='{$a['adaccount']['id']}' selected data-pixel='{$a['pixel_id']}'>{$a['adaccount']['name']}, ({$a['pixel_id']})</option>"; 
                                                ?>
                                            <?php else: ?>
                                                <?php 
                                                    if (!empty($a['pixel_id']))
                                                        echo "<option value='{$a['adaccount']['id']}' data-pixel='{$a['pixel_id']}'>{$a['adaccount']['name']}, ({$a['pixel_id']})</option>";
                                                ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <input class="form-control" name="ca" id="ca" type="text" value="<?php echo $settings["ca"]; ?>" placeholder="Select ad account or enter pixel ID manually">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">2nd FB Account Pixel ID:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/settings-video2.html" >?</a></div>
                            <div class="field">
                                <div class="select-editable">
                                    <select id="ad_accounts2" name="ad_account2" class="form-control ad_account">
                                        <option value=""></option>
                                        <?php foreach ($ad_accounts as $a) : ?>
                                            <?php if (!empty($a['pixel_id']) && $settings["ad_account2"] == $a['adaccount']['id']) : ?>
                                                <?php 
                                                    if (!empty($a['pixel_id']))
                                                        echo "<option value='{$a['adaccount']['id']}' selected data-pixel='{$a['pixel_id']}'>{$a['adaccount']['name']}, ({$a['pixel_id']})</option>"; 
                                                ?>
                                            <?php else: ?>
                                                <?php 
                                                    if (!empty($a['pixel_id']))
                                                        echo "<option value='{$a['adaccount']['id']}' data-pixel='{$a['pixel_id']}'>{$a['adaccount']['name']}, ({$a['pixel_id']})</option>";
                                                ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <input class="form-control" name="ca_2nd" id="ca_2nd" type="text" value="<?php echo $settings["ca_2nd"]; ?>" placeholder="Select ad account or enter pixel ID manually">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Catalog ID (optional):</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/catalog-placeholder.html" >?</a></div>
                            <div class="field">
                                <input class="form-control" name="pvtc" type="text" value="<?php echo $settings["pvtc"]; ?>">
                            </div>                            
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">AddToCart and Purchase Events send:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop dumb"></div>
                            <div class="field">
                                <select name="pes" class="form-control">
                                    <?php
                                        $arrPes = array(
                                            'pdid'=>'Product ID',
                                            'pdid_only'=>'Product ID [content=product]'
                                        );
                   
                                        foreach ($arrPes as $kpes=>$vpes) {
                                            if ($settings["pes"] == $kpes) {
                                                $selected = " selected";
                                            } else {
                                                $selected = "";
                                            }
                                            echo '<option value="' . $kpes . '"' . $selected . '>' . $vpes . '</option>';
                                        }
                                    ?>
                                </select>          
                            </div>                  
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Global Key Page View Pixel ID (optional):</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/settings-video2.html" >?</a></div>
                            <div class="field">
                                <input class="form-control" name="kpv" type="text" value="<?php echo $settings["kpv"]; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Global Add to cart Pixel ID (optional):</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop dumb"></div>
                            <div class="field">
                                <input class="form-control" name="atc" type="text" value="<?php echo $settings["atc"]; ?>">                            
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Global Checkout Pixel ID:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/settings-video2.html" >?</a></div>
                            <div class="field">
                                <input class="form-control" name="global" type="text" value="<?php echo $settings["global"]; ?>">                            
                            </div>                            
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Select Cart Mode:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/settings-video2.html" >?</a></div>
                            <div class="field">
                                <select class="form-control" name="ajax">
                                    <option value="0">Regular Cart</option>
                                    <option <?php if ($settings["ajax"] == 1) { echo 'selected'; }; ?> value="1">Ajax Cart</option>
                                </select>          
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Niche Checkout Pixel Reports Value:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop dumb"></div>
                            <div class="field">
                                <select class="form-control" name="conv_value">
                                    <option value="0">No (Default)</option>
                                    <option <?php if ($settings["conv_value"] == 1) { echo 'selected'; }; ?> value="1">Yes</option>                                
                                </select>          
                            </div>                                              
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">FB Pixel events (VC, ATC) report Value:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop dumb"></div>
                            <div class="field">
                                <select class="form-control" name="fb_value">
                                    <option value="0">No (Default)</option>
                                    <option <?php if ($settings["fb_value"] == 1) { echo 'selected'; }; ?> value="1">Yes</option>                                
                                </select>                                                        
                            </div>
                        </div>
                    </div>
                    <!--<div class="form-group row">
                        <label class="col-md-5 form-control-label">Trackify Pixel Fires:</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop dumb"></div>
                            <div class="field">
                                <input type="checkbox" name="fire" <?php if ($settings["fire"] == 1) { echo 'checked'; }; ?> class="bootstrap-switch" data-size="mini">
                            </div>
                        </div>
                    </div>-->
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Time Delay on VC event:</label>
                        <div class="col-md-7">
                            <div class="field">
                                <input class="form-control" name="vc_delay" type="number" value="<?php echo $settings["vc_delay"]; ?>"> Seconds
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Pinterest Pixel ID (optional):</label>
                        <div class="col-md-7 help_wrap">
                            <div class="redre-help-pop dumb"></div>
                            <div class="field">
                                <input class="form-control" name="pinterest_pixel" type="text" value="<?php echo $settings["pinterest_pixel"]; ?>">                            
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-5 form-control-label">Trackify Pixel Fires:</label>
                        <div class="col-md-7">                            
                            <div class="field">
                                <div class="radio_wrap" style="padding-right: 10px;"><input value="1" type="radio" name="fire" <?php if ($settings["fire"] == 1) { echo 'checked'; }; ?> class="bootstrap-switch_" data-size="mini"><span>On</span></div>
                                <div class="radio_wrap"><input value="0" type="radio" name="fire" <?php if ($settings["fire"] == 0) { echo 'checked'; }; ?> class="bootstrap-switch_" data-size="mini"><span>Off</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <button name="submit" type="submit" class="btn btn-primary">Save</button>
                        </div>
                        <?php if ($settings["upgraded"] != 1) : ?>
                        <div class="col-md-6 help_wrap upgrade_wrap">
                            <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/settings-platinum-upgrade.html" >?</a></div>
                            <div class="field">
                                <a href="<?php echo base_url() . "Auth/upgrade"; ?>" name="upgrade" class="btn btn-primary upgrade">Upgrade to Platinum</a>
                            </div>                            
                        </div>
                        <?php endif; ?>
                    </div>
                <?php echo form_close(); ?>                

                <!-- Settings Info Modal -->
                <div class="modal fade vm-modal" id="settings_info_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4 class="modal-title" id="myModalLabel">Trackify</h4>
                            </div>
                            <div class="modal-body">
                                Awesome, your Settings were saved! If you have previously placed any Trackify code in the <a style="color: rgb(0, 136, 204); text-decoration: underline;" target="_blank" href="https://<?php echo $_SESSION["shop"]; ?>/admin/settings/checkout">Checkout - Settings field</a> in your Shopify Admin, please go there now and REMOVE all Trackify code (including any Facebook pixel code that was part of the Trackify code before!)
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (isset($_GET["redirect"])) : ?>
                    <div class="modal fade" id="settings_info2_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">Trackify</h4>
                                </div>
                                <div class="modal-body">
                                    You need to configure settings first to get access to other pages.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        $("#settings_info2_modal").modal();
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php if (isset($show_modal) && $show_modal == 1) : ?>
<script>
    $("#settings_info_modal").modal();
</script>
<?php endif; ?>

<script>
    jQuery(document).ready(function($) {
        $("#ad_accounts").change(function(e) {
            $("#ca").val($("option:selected", this).attr("data-pixel"));
        });
        $("#ad_accounts2").change(function(e) {
            $("#ca_2nd").val($("option:selected", this).attr("data-pixel"));
        });        

        $("#business_accounts").change(function(e) {
            if ($(this).val() == "") return;
            $.ajax({
                url: base_url + 'MY_Facebook/get_adaccounts_by_business/' + $(this).val(),
                type: 'get',
                success: function(response) {
                    var html = '<option value=""></option>';
                    if (response.length > 0) {
                        pixel_id = $("#ca").val();
                        for (i = 0; i < response.length; i++) {
                            p = response[i]["pixel_id"];
                            if (pixel_id == p) {
                                html += '<option value="' + response[i]["adaccount"]["id"] + '" data-pixel="' + p + '" selected>' + response[i]["adaccount"]["name"] + ', (' + p + ')</option>';
                            } else {
                                html += '<option value="' + response[i]["adaccount"]["id"] + '" data-pixel="' + p + '">' + response[i]["adaccount"]["name"] + ', (' + p + ')</option>';
                            }
                        }
                    }
                    $("#ad_accounts").html(html);
                }
            });
        });

        //$("#business_accounts").trigger("change");
    });    
</script>