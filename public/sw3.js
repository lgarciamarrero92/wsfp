this.addEventListener('fetch',(evt)=>{
    evt.respondWith(
        caches.match(evt.request).then((resp)=>{
            return resp || fetch(evt.request).then((response)=>{
                caches.open('v1').then((cache)=>{
                    cache.put(evt.request,response.clone())
                })
                return response
            })
        })
    )
    console.log('new fetch cached'+evt.request)
})