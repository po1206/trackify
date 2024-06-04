<div id="installation_page">
    <div id="modal-installation" class="modal fade" style="opacity:1;padding-top:10%;display:block;">
        <div class="modal-dialog">
            <div class="modal-content">
                <?php echo form_open('auth/shopify_oauth?welcome=1', array('id' => 'form-installation')); ?>
                    <div class="modal-header">
                        <h3 class="modal-title">Enter Your Shopify URL</h3>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control install-text" name="shop" placeholder="myshop.myshopify.com" required/>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btnstartshopify">Install</button>
                    </div>
                <?php echo form_close(); ?>
            </div> 
        </div> 
    </div>
    <script>
        $("#form-installation").submit(function(e){
            var redirect_shop = $(this).find("input[name=shop]");
            var shop = $(redirect_shop).val().trim();
            shop = shop.replace(/ /g, "");
            
            if (shop.length == 0 ) {
                e.preventDefault();
                return false;
            }
     
            shop = shop.replace("http://", "");
            shop = shop.replace("https://", "");
            $(redirect_shop).val(shop);            
        });
    </script>
</div>
