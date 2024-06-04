<div id="content">
    <div id="tracking_page">
        <div class="alert alert-success divFirstExplanation">
            <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
            <strong>Important:</strong> This app manages tracking pixel
        </div>
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        <?php if ($settings["upgraded"] == 1) : ?>
        <div class="switcher_div">
            <div class="btn-group">
              <a href="<?php echo base_url("track/switch_tracking/conversion"); ?>" type="button" class="btn btn-default <?php if ($settings["track_with"] == "conversion") echo "btn-primary active"; ?>">Conversion Pixel</a>
              <a href="<?php echo base_url("track/switch_tracking/facebook"); ?>" type="button" class="btn btn-default <?php if ($settings["track_with"] == "facebook") echo "btn-primary active"; ?>">Facebook Pixel</a>
            </div>
        </div>
        <?php endif; ?>
        <div class="row-fluid">
            <div class="box">
                <div class="box-content">
                    <?php echo form_open("track/add_tcode/conversion"); ?>                
                        <table class="table table-bordered table-striped table-condensed">
                            <tr data-toggle="collapse" data-target="#accordion" class="clickable mobile">
                                <th class="sixth help_wrap with_text"><span>Tag: ( Prefix: rr_track_ )</span>
                                    <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax">
                                        <a href="<?php echo base_url() ;?>assets/videos/contextual_videos/ajax/alltags-create-tag-and-pixels.html" >?</a>
                                    </div>
                                </th>
                                <th class="sixth">Tracking code for product page:</th>
                                <th class="sixth">Tracking code for cart:</th>
                                <th class="sixth">Tracking code for checkout:</th>
                                <th class="sixth">Action:</th>
                            </tr>
                            <tr data-toggle="collapse" data-target="#accordion" class="clickable">
                                <td class="sixth">
                                    <span class="hide-desktop">Tag:<br></span>
                                    <input class="form-control"  type="text" name="tag" required>
                                </td>
                                <td class="sixth">
                                    <span class="hide-desktop">Tracking code for product page:<br></span>
                                    <input class="form-control" type="text" name="tcode0">
                                </td>
                                <td class="sixth">
                                    <span class="hide-desktop">Tracking code for cart:<br></span>
                                    <input class="form-control"  type="text" name="tcode1">
                                </td>
                                <td class="sixth">
                                    <span class="hide-desktop">Tracking code for checkout:<br></span>
                                    <input class="form-control"  type="text" name="tcode2">
                                </td>
                                <td  class="sixth">
                                    <span class="hide-desktop">Action:<br></span>
                                    <button type="submit" class="btn btn-primary">Add Pixel</button>
                                </td>
                            </tr>
                        </table>                    
                    <?php echo form_close(); ?>

                    <div class="clearfix"></div>
                </div>
            </div>
            
            <div class="fb_wrap fetch_btn_wrap">
                <div class="btn-wrap">
                    <button class="btn btn-primary" type="button" id="fetch_tags">Fetch all rr_track tags</button>
                </div>                
            </div>
            <div id="divTableResults" class="box-content">
                <div id="tableResults" class="row-fluid">
                    <table class="table table-striped table-condensed" id="fb_table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Tag</th>
                                <th>Product page Pixel</th>
                                <th>Cart page Pixel</th>
                                <th>Checkout page Pixel</th>
                                <th class="fb_action">Action</th>
                            </tr>
                        </thead>
                        <tbody>                    
                            <?php foreach ($tcodes as $row) : ?>
                            <tr>
                                <td></td>
                                <td><a href="<?php echo base_url() . "manage/" . $row["tags"]; ?>"><?php echo $row["tags"];?></a></td>
                                <td><?php echo $row["code0"];?> - Fires: <?php echo $row["cd0"];?></td>
                                <td><?php echo $row["code1"];?> - Fires: <?php echo $row["cd1"];?></td>
                                <td><?php echo $row["code2"];?> - Fires: <?php echo $row["cd2"];?></td>
                                <td class="fb_action">
                                    <a href="<?php echo base_url() . 'manage/' . $row['tags']; ?>" class="btn btn-primary" id="act-<?php echo $row["id"]; ?>">Edit</a>
                                    <a href="<?php echo base_url() . 'track/delete_tcode/' . $row['id']; ?>" class="btn btn-danger" id="act-<?php echo $row["id"]; ?>">Remove</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <script>
                        table = $("#fb_table").DataTable({
                                    "bLengthChange": true,
                                    "dom": "lrtip",
                                    "searching": false,
                                    "pageLength": 10,
                                    "aaSorting": [],
                                    "aoColumnDefs": [
                                        { 'bSortable': false, 'aTargets': [0, 5] }
                                    ]
                        });
                    </script>

                </div>
            </div>

        </div>
    </div>

    <div class="modal-overlay" id="AddModal2" style="display: none;">
        <section class="quick-modal">
            <div class="table-cell">
                <div class="product">
                    <div class="center" id="moq">              
                        <p>Congratulation! You have successfully installed Trackify in your site</p>            
                    </div>
                </div>         
            </div>
            <span class="close" onclick="$('#AddModal2').fadeOut();"></span>    
        </section>
    </div>

    <?php
      
    if (isset($_GET['install']) && ($_GET['install'])== 'true') {
        echo "
            <script> 
                $('#AddModal2').fadeIn();
     
                $('body').click(function(e) {
                    if($(e.target).is('#AddModal2')){
                        e.preventDefault();
                        return;
                    }
                    $('#AddModal2').fadeOut();
                }); 
            </script>";
    }
    ?>
</div>