<div id="content">
    <div id="add_feed_page">
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <div class="fb-side-box">
            <div class="row fb-title-wrap">
                <div class="col-sm-9">
                    <h3>Add New Product Feed to <?php echo $catalog_name; ?></h3>
                </div>
                <div class="col-sm-3 text-right">
                    <a href="<?php echo base_url() . "facebook-feeds"; ?>" class="btn btn-primary">FB Product Feeds</a>
                </div>
            </div>
            <div class="box">
                <div class="box-content ca_form">
                    <?php echo form_open("create-feed/{$catalog_id}", array('id' => 'sform')); ?>
                        <div class="form-group">
                            <label>Feed Name</label>
                            <input type="text" class="form-control" name="feed_name" value="<?php echo "New Product Feed for {$catalog_name} - " . date("m/d/Y"); ?>" required/>
                        </div>

                        <div class="form-group">
                            <label>Feed URL</label>
                            <input type="text" class="form-control" name="feed_url" placeholder="Enter your feed's URL" required/>
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
            
        });
    });

</script>
