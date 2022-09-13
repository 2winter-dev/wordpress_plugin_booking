

const Macro_js = {

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
                }).catch((e)=>alert(e.msg || '操作未完成'))
            })
        }else{
            alert('初始化失败')
        }

    }


}