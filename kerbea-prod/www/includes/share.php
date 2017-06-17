<div class="social-container no-tablette no-mobile">
    <span>partager</span>
    <button style="background: none; border: 0; cursor: pointer;" class="social facebook" title="Partager sur Facebook" onclick="window.open('http://www.facebook.com/sharer.php?u=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>', 'facebook', 'height=306,width=534,scrollbars=1');return false;">
        <img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fb-black.png" alt=""/>
    </button>
    <button class="social twitter" style="background: none; border: 0; cursor: pointer;" title="Partager sur Twitter" onclick="window.open('http://www.twitter.com/share?url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>&text=Trouvez%20votre%20maisons', 'twitter', 'height=306,width=650,scrollbars=1');return false;">
        <img src="<?php echo RESSOURCE_URL; ?>/images/pictos/tweet-black.png" alt=""/>
    </button>
    <a class="social" href="https://plus.google.com/share?url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;"><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/gplus-black.png" alt=""/></a>
</div>