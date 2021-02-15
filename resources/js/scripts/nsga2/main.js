import {spawn,Worker,Thread} from 'threads'
const EvtEmitter = require('events')
class nsga2{
    constructor(){
        this.listen = new EvtEmitter()
    }
    async solve(n,gen,model){
        let worker = await spawn(new Worker('./worker.js',{name: 'js/nsga2'}))
        worker.progress().subscribe( (val)=>{
            this.listen.emit('progress',val)
        })
        let results = await worker.nsga2(n,gen,model)
        await Thread.terminate(worker)
        return results
    }
    
}
export default nsga2;

