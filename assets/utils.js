/**
 * * @author  omibeaver
 * Admin Booking Manage pane JS
 * @type {{changeBookingStatus(*): void}}
 */
const omiJS = {
    changeBookingStatus(post_id){
        if(winter_ajax_obj){
            let formData = new FormData();
            formData.append('_ajax_nonce',winter_ajax_obj.nonce);
            formData.append('action',"change_booking_status");
            formData.append('post_id',post_id)
            fetch(winter_ajax_obj.ajax_url, {
                    method:'POST',
                    body:formData
                }
            ).then((body)=>{
                body.json().then((data)=>{
                    alert(data.msg);
                    setTimeout(()=>{location.reload()},1000)
                }).catch((e)=>alert(e.msg))
            })
        }else{
            alert('init failed')
        }

    }


}