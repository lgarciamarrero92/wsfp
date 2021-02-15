import {spawn,Worker,Thread} from 'threads'
const EvtEmitter = require('events')
class cfc{
    constructor(){
        this.data = {

        }
        this.progress = 0
        this.listen = new EvtEmitter()
    }
    async get(data){
        this.progress = 0
        let worker = await spawn(new Worker('./worker.js',{name: 'js/coefficients'}))
        worker.progress().subscribe( (val)=>{
            this.listen.emit('progress',val)
            this.progress = val
        })
        this.data = await worker.get(data)
        await Thread.terminate(worker)
        return this.data
    }
    
}
export default cfc;



