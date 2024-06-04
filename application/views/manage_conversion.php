<div id="content">
    <div id="manage_page">
        <div class="alert alert-success divFirstExplanation">
            <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
            <strong>Important:</strong> You are managing tracking pixel : <a href="<?php echo base_url() . "manage/" . $tag; ?>"><?php echo $tag; ?></a>
        </div>
        <?php if (!empty($_SESSION['message_display'])) : ?>
            <div class="alert alert-success divFirstExplanation">
                <button data-dismiss="alert" class="close closeClickExplanation" type="button">×</button>
                <strong><?php echo $_SESSION['message_display']; $_SESSION['message_display'] = ''; ?></strong>
            </div>
        <?php endif; ?>
        
        <div class="row">        
            <div class="col-sm-12">
                <?php echo form_open("Track/update_tcode/" . $tcode["id"] . "/?track_with=conversion"); ?>                
                    <table class="table table-bordered table-striped table-condensed">
                        <tr>
                            <td class="fifth">
                                <label>Tag:</label>
                                <input class="form-control" type="text" value="<?php echo $tcode['tags'];?>" name="tag">
                                <input type="hidden" value="<?php echo $tcode['tags'];?>" name="old_tag"/>
                            </td>
                            <td class="fifth">
                                <label>Tracking code for product page:</label>
                                <input class="form-control" type="text" value="<?php echo $tcode['code0'];?>" name="tcode0">
                            </td>
                            <td class="fifth">
                                <label>Tracking code for cart:</label>
                                <input class="form-control" type="text" value="<?php echo $tcode['code1'];?>" name="tcode1">
                            </td>
                            <td class="fifth">
                                <label>Tracking code for checkout:</label>
                                
                                <div class="help_wrap">
                                    <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/tagedit-editing.html">?</a></div>                                    
                                    <div class="field">
                                        <input class="form-control" name="tcode2" type="text" value="<?php echo $tcode["code2"]; ?>">
                                    </div>                            
                                </div>
                            </td>
                            <td class="fifth"><button type="submit" class="btn btn-primary btn-update-tcode" id="act-<?php echo $tcode["id"]; ?>">Update</button></td>
                        </tr>                    
                    </table>                    
                <?php echo form_close(); ?>
            </div>
        </div>

        <div class="row mb30">
            <div class="col-md-12">
                <div class="select_wrap help_wrap">
                    <div class="redre-help-pop" data-hasqtip="ajax" aria-describedby="qtip-ajax"><a href="<?php echo base_url(); ?>assets/videos/contextual_videos/ajax/tagedit-basics.html" >?</a></div>
                    <select class="form-control select2" name="crd" id="crd">
                        <option value="0">Select Product to Add..</option>
                        <?php foreach ($products as $product) : ?>
                            <?php if (strpos($product['tags'], $tag) === false) : ?>
                                <option value="<?php echo base_url() . "manage/{$tag}?pid={$product['id']}&add={$product['title']}"; ?>"><?php echo $product['title']; ?></option>                                
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <a class="btn btn-primary" id="add_product">Add Product</a>
            </div>
        </div>
        
        <div class="fb_row">
            <?php if (count($warning_products) > 0) : ?>
                <p class="fb_alert" id="mwar">Notice: the following products have more than 1 niche tracking tag.</p>
                <table class="table table-bordered table-striped table-condensed">
                    <tbody>
                        <tr>
                            <th class="text-center">Action</th>
                            <th class="text-center">Image</th>
                            <th>Product</th>
                            <th>All Tags</th>
                        </tr>
                        <?php foreach ($warning_products as $product) : ?>
                        <tr>
                            <td class="text-center">
                                <a href="<?php echo base_url() . "/manage/$tag?pid={$product['id']}&remove={$product['title']}"; ?>" class="btn btn-danger">Remove</a>                                
                            </td>
                            <td class="text-center">
                                <?php
                                    if ($product['image']) {
                                        $extension_pos = strrpos($product['image']['src'], '.'); // find position of the last dot, so where the extension starts
                                        $thumb = substr($product['image']['src'], 0, $extension_pos) . '_small' . substr($product['image']['src'], $extension_pos);
                                    } else {
                                        $thumb = "no-image.jpg";
                                    }
                                ?>
                                <img src="<?php echo $thumb; ?>" />
                            </td>
                            <td>
                                <?php echo '<a target="_blank" href="//' . $_SESSION['shop'] . '/admin/products/' . $product['id'] . '">' . $product['title'] . '</a>'; ?>        
                            </td>
                            <td valign="middle"><?php echo $product['tags']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table> 
            <?php endif; ?>

            <?php if (count($tagged_products) > 0) : ?>
                <h5>Tagged Products:</h5>
                <table class="table table-bordered table-striped table-condensed">
                    <tbody>
                        <tr>
                            <th class="text-center">Action</th>
                            <th class="text-center">Image</th>
                            <th>Product</th>
                            <th>All Tags</th>
                        </tr>
                        <?php foreach ($tagged_products as $product) : ?>
                        <tr>
                            <td class="text-center">
                                <a href="<?php echo base_url() . "Track/remove_tag/$tag?pid=" . $product['id']; ?>" class="btn btn-danger">Remove</a>
                            </td>
                            <td class="text-center">
                                <?php
                                    if ($product['image']) {
                                        $extension_pos = strrpos($product['image']['src'], '.'); // find position of the last dot, so where the extension starts
                                        $thumb = substr($product['image']['src'], 0, $extension_pos) . '_small' . substr($product['image']['src'], $extension_pos);
                                    } else {
                                        $thumb = "no-image.jpg";
                                    }
                                ?>
                                <img src="<?php echo $thumb; ?>" />
                            </td>
                            <td>
                                <?php echo '<a target="_blank" href="//' . $_SESSION['shop'] . '/admin/products/' . $product['id'] . '">' . $product['title'] . '</a>'; ?>        
                            </td>
                            <td valign="middle"><?php echo $product['tags']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table> 
            <?php else: ?>
                <h3>This tracking tag has not been assigned to any products yet.</h3>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {        
        $("#add_product").click(function(e) {
            if ($("#crd").val() != "0") {
                location.href = $("#crd").val();
            }            
        });
    });
    
</script>