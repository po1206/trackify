        </div>            
    <div class="clearfix"></div>
    
    <?php if(isset($_GET["welcome"])): ?>
    <script>
    jQuery(document).ready(function($) {
        return;        
        $(".fancybox").on("click", function(){
            $.fancybox({
              href: this.href,
              type: $(this).data("type")
            }); // fancybox
            return false
        }); // on
       $(".welcomepopup").trigger('click');
    }); // ready
    </script>
    <a style="display: none;" class="fancybox welcomepopup" data-type="swf" href="<?php echo base_url(); ?>assets/videos/jwplayer.swf?file=<?php echo base_url(); ?>assets/videos/trackify-intro2.mp4&autostart=true" title="local video mp4"><img src="<?php echo base_url(); ?>assets/images/video1.jpg" /></a>
    <?php endif; ?>
    
    <div id="footer"></div>    
    
</div>
</body>

</html>