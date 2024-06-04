<div id="content">
    <div id="create_catalog_page">
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <div class="fb-side-box">
            <div class="row fb-title-wrap">
                <div class="col-sm-9">
                    <h3>Create Catalog</h3>
                </div>
                <div class="col-sm-3 text-right">
                    <a href="<?php echo base_url() . "facebook-feeds/{$business_id}"; ?>" class="btn btn-primary">Facebook Product Catalogs</a>
                </div>
            </div>
            <div class="box">
                <div class="box-content ca_form">
                    <?php echo form_open("create-catalog/{$business_id}", array('id' => 'sform')); ?>
                        <div class="form-group">
                            <label>Catalog Name</label>
                            <input type="text" class="form-control" name="catalog_name" value="<?php echo $shop["name"] . " - Trackify"; ?>" required/>
                        </div>

                        <div class="form-group">
                            <label>Feed Name</label>
                            <input type="text" class="form-control" name="feed_name" value="<?php echo $shop["name"] . " - Trackify - " . date("m/d/Y"); ?>" required/>
                        </div>

                        <div class="form-group">
                            <label>Feed URL</label>
                            <?php 
                                if (!empty($channels)) {
                                    if ($channels[0]["feedurl_changed"] == 1) {
                                        $feed_url = base_url() . "feeds/" . bin2hex(base64_encode($channels[0]["id"]));
                                    } else {
                                        $feed_url = $channels[0]["feed_file"];
                                    }
                                } else {
                                    $feed_url = "";
                                }
                            ?>
                            <input type="text" class="form-control" name="feed_url" placeholder="Enter your feed's URL" value="<?php echo $feed_url; ?>" required/>
                        </div>

                        <div class="form-group">
                            <label>Pixel ID</label>
                            <div class="select-editable">
                                <select id="ad_accounts" name="ad_account" class="form-control ad_account" style="width: 100%;">
                                    <option value=""></option>
                                    <?php 
                                        foreach ($ad_accounts as $a) {
                                            if (!empty($a['pixel_id']))
                                                echo "<option value='{$a['adaccount']['id']}' data-pixel='{$a['pixel_id']}'>{$a['adaccount']['name']}, ({$a['pixel_id']})</option>";
                                        }
                                    ?>                                    
                                </select>
                                <input class="form-control" name="pixel_id" id="pixel_id" type="text" value="<?php echo $settings['ca']; ?>" placeholder="Select ad account or enter pixel ID manually" required>
                            </div>
                        </div>                        

                        <div class="form-group">
                           <input name="submit" type="submit" value="Create Catalog" class="btn btn-primary" id="submit"/>
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
            
        });

        $("#ad_accounts").change(function(e) {
            $("#pixel_id").val($("option:selected", this).attr("data-pixel"));
        });
    });

</script>
