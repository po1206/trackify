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
                <h3>Create a Lookalike Audience</h3>
                <a href="<?php echo base_url() . "MY_Facebook/custom_audiences"; ?>" class="btn btn-primary">FB Audiences</a>
            </div>
            <div class="box">
                <div class="box-content ca_form">
                    <?php echo form_open('MY_Facebook/create_audience/lookalike', array('id' => 'sform')); ?>
                        <input type="hidden" name="ad_account" value="<?php echo $ad_account_id; ?>" />
                        <div class="form-group" style="display: none;">
                            <label class="field_title">Name</label>
                            <input type="text" class="form-control" name="custom_audience_name"/>
                        </div>

                        <div class="form-group">
                            <label class="field_title">Source</label>
                            <small>Select an existing audience to model your lookalike audience on.</small>
                            <select id="audiences" name="audiences" class="form-control select2" required>
                                <option value=''>Select an existing audience</option>
                                <?php foreach ($audiences as $a) : ?>
                                    <?php echo "<option value='{$a['id']}'>{$a['name']}</option>"; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="field_title">Country</label>
                            <small>Select the country for your lookalike audience.</small>
                            <?php echo country_html(0); ?>
                        </div>

                        <div class="form-group clearfix">
                            <label class="field_title">Audience Size</label>
                            <small>Audience size ranges from 1% to 10% of the total population in the country you choose, with 1% being those who most closely match your source.</small>
                            <div class="fb-btn-group">
                                <?php foreach ($audience_size as $l) : ?>                            
                                    <div class="ck-button audience_size">
                                        <label><input type="checkbox" name="audience_size[]" value="<?php echo $l; ?>"><span><?php echo $l; ?>%</span></label>
                                    </div>
                                <?php endforeach; ?>
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
            checked = $("input[name='audience_size[]']:checked").length;
            if (!checked) {
                alert("You must select audience size.");
                return false;
            }
        });
    });

</script>
