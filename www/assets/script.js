function iframesize(){
    iframe=document.getElementById('iframe');
    header=document.getElementById('header');

    if(iframe != null){
        iframe.style.height = window.innerHeight - header.offsetHeight-10 + 'px';
    }

}
iframesize();

window.addEventListener('resize', function(event) {
 iframesize();
}, true);