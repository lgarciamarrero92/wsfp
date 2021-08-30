import {expose} from 'threads'
import {Observable} from 'observable-fns'
import {Subject} from 'threads/observable'
const _ = require('lodash');

let subject = new Subject()

const obj = {
    model: {},
    mE: null,
    mC: null,
    mWA: null,
    mSA: null,
    progress(){
        return Observable.from(subject) 
    },
    generateRandomSolution(model){
        let zones = Object.keys(model)
        let sol = {
            'energy': 0.0,
            'costs': 0.0,
            'solarArea': 0.0,
            'windArea': 0.0,
            'details': {

            }
        }
        let test = {}
        for (let i = 0; i < zones.length; i++) {
            const item = zones[i]
            let fc = Object.keys(model[item])
            let r = Math.floor(Math.random()*fc.length)
            let idt = fc[r]
            sol.energy += model[item][idt].energy
            sol.costs += model[item][idt].costs
            sol.solarArea += model[item][idt].solarArea
            sol.windArea += model[item][idt].windArea
            test[item] = {
                'energy': model[item][idt].energy,
                'costs': model[item][idt].costs,
                'facility': idt
            }
            sol.details[item] = model[item][idt]
        }
        let fc = Object.keys(test)
        let dominated = []
        for(var i = 0 ; i < fc.length; i++ ){
            var belongs = true
            for(var j = 0 ; (j < fc.length && j != i); j++ ){
                if( (test[fc[j]].energy >= test[fc[i]].energy && test[fc[j]].costs < test[fc[i]].costs) ||
                    (test[fc[j]].energy > test[fc[i]].energy && test[fc[j]].costs <= test[fc[i]].costs)
                ){
                    belongs = false
                    break
                }
            }
            if(belongs == false ){
                dominated.push({
                    'zone': fc[i],
                    'facility': test[fc[i]].facility
                })
            }
        }
        return sol;
    },
    domine(m1,m2){
        if(m1["energy"] > m2["energy"] && m1["costs"] <= m2["costs"] && m1["solarArea"] <= m2["solarArea"] && m1["windArea"] <= m2["windArea"]) return true;
        if(m1["energy"] >= m2["energy"] && m1["costs"] < m2["costs"] && m1["solarArea"] <= m2["solarArea"] && m1["windArea"] <= m2["windArea"]) return true;
        if(m1["energy"] >= m2["energy"] && m1["costs"] <= m2["costs"] && m1["solarArea"] < m2["solarArea"] && m1["windArea"] <= m2["windArea"]) return true;
        if(m1["energy"] >= m2["energy"] && m1["costs"] <= m2["costs"] && m1["solarArea"] <= m2["solarArea"] && m1["windArea"] < m2["windArea"]) return true;
        return false;
    },
    //Begin NSGA-II utility functions
    fastNonDominatedSort(P){
        let F = []
        P.forEach(p=>{
            p["domineSet"] = []
            p["domination"] = 0
            P.forEach(q=>{
                if(obj.domine(p,q)){
                    p["domineSet"].push(q)
                }else if(obj.domine(q,p)){
                    p["domination"]+=1
                }
            })
            if(p["domination"] == 0){
                p["rank"] = 1
                F.push(p)
            }
        })
        let i = 1
        while(F.length){
            let Q = []
            F.forEach(p=>{
                p["domineSet"].forEach(q=>{
                    q["domination"] -= 1
                    if(q["domination"] == 0){
                        q["rank"] = i+1
                        Q.push(q)
                    }
                })
            })
            i = i + 1
            F = Q
        }
    },
    crowdingDistanceAssignment(P){
        let l = P.length
        const oo = 1000000000000000000
        P.forEach(p=>{
            p["distance"] = 0.0
        })
        //Energy
        P.sort((a,b)=>{
            return (a.energy - b.energy)
        })
        P[0].distance = P[l-1].distance = oo
        for( let i = 1 ; i < l-1 ; i++ ){
            if(P[i].distance != oo )
                P[i].distance += (P[i+1].energy - P[i-1].energy)/obj.mE
        }
        //Costs
        P.sort((a,b)=>{
            return (a.costs - b.costs)
        })
        P[0].distance = P[l-1].distance = oo
        for( let i = 1 ; i < l-1 ; i++ ){
            if(P[i].distance != oo )
                P[i].distance += (P[i+1].costs - P[i-1].costs)/obj.mC
        }
        
        //solarArea
        P.sort((a,b)=>{
            return (a.solarArea - b.solarArea)
        })
        P[0].distance = P[l-1].distance = oo
        for( let i = 1 ; i < l-1 ; i++ ){
            if(P[i].distance != oo )
                P[i].distance += (P[i+1].solarArea - P[i-1].solarArea)/obj.mSA
        }
        //windArea
        P.sort((a,b)=>{
            return (a.windArea - b.windArea)
        })
        P[0].distance = P[l-1].distance = oo
        for( let i = 1 ; i < l-1 ; i++ ){
            if(P[i].distance != oo )
                P[i].distance += (P[i+1].windArea - P[i-1].windArea)/obj.mWA
        }
        
    },
    betterByFitness(s1,s2){
        if(s1.rank != s2.rank ){
            return s1.rank < s2.rank;
        }
        return s1.distance > s2.distance;
    },
    binaryTournamentSelection(P){
        let Q = []
        for(let i = 0 ; i < P.length; i++ ){
            let l = Math.floor(Math.random()*P.length)
            let r = Math.floor(Math.random()*P.length)
            if(obj.betterByFitness(P[l],P[r])){
                Q.push(P[l])
            }else{
                Q.push(P[r])
            }
        }
        return Q;
    },
    crossover(p1,p2){

        let fc = Object.keys(p1.details)
        let l = Math.floor(Math.random()*fc.length)

        let c1 = _.cloneDeep(p1)
        let c2 = _.cloneDeep(p2)
        
        for(let i = 0 ; i < fc.length ; i++ ){
            if(Math.random() >= 0.5 ){
                c2.details[fc[i]] = p1.details[fc[i]]
                c1.details[fc[i]] = p2.details[fc[i]]
            }
        }
        
        //Recalculate objectives
        c1.energy = c2.energy = c1.costs = c2.costs = c1.solarArea = c2.solarArea = c1.windArea = c2.windArea = 0
        for(let i = 0 ; i < fc.length ; i++ ){
            c1.energy += c1.details[fc[i]].energy
            c2.energy += c2.details[fc[i]].energy   
            c1.costs += c1.details[fc[i]].costs
            c2.costs += c2.details[fc[i]].costs 
            c1.solarArea += c1.details[fc[i]].solarArea
            c2.solarArea += c2.details[fc[i]].solarArea 
            c1.windArea += c1.details[fc[i]].windArea
            c2.windArea += c2.details[fc[i]].windArea        
        }
        return [c1,c2];
    },
    mutate(children){
        let fc = Object.keys(children[0].details)
        let mutated = []
        children.forEach(element=>{
            
            let child = _.cloneDeep(element)
        
            for(let l = 0 ; l < fc.length ; l++ ){
                if(Math.random() <= 1.0/fc.length ){
                    const item = fc[l]
                    let ft = Object.keys(obj.model[item])
                    let r = Math.floor(Math.random()*ft.length)
                    let idt = ft[r]
                    
                    child.energy -= child.details[item].energy
                    child.costs -= child.details[item].costs
                    child.solarArea -= child.details[item].solarArea
                    child.windArea -= child.details[item].windArea

                    child.energy += obj.model[item][idt].energy
                    child.costs += obj.model[item][idt].costs
                    child.solarArea += obj.model[item][idt].solarArea
                    child.windArea += obj.model[item][idt].windArea
                    child.details[item] = _.cloneDeep(obj.model[item][idt])
                }
            }
            mutated.push(child)
        })
        return mutated;
    },
    makeNewPop(P){
        let Parents = obj.binaryTournamentSelection(P)
        let Q = []
        for(let i = 0 ; i < Parents.length ; i+=2 ){
            var children = {}
            if(i+1<Parents.length){
                children = obj.crossover(Parents[i],Parents[i+1])
            }
            else{
                children = obj.crossover(Parents[i],Parents[i])
            }
            let mutated = obj.mutate(children)
            Q.push(mutated[0])
            Q.push(mutated[1])
        }
        return Q;
    },
    nsga2(n,gen,model){
        //Calculate maximum values
        let zones = Object.keys(model)
        obj.mE = obj.mC = obj.mSA = obj.mWA = 0.0
        for (let i = 0; i < zones.length; i++) {
            let mE = 0.0,mC = 0.0,mSA = 0.0, mWA = 0.0;
            const item = zones[i]
            let fc = Object.keys(model[item])
            for(let j = 0 ; j < fc.length ; j++ ){
                if(model[item][fc[j]]['energy'] >= mE){
                    mE = model[item][fc[j]]['energy']
                }
                if(model[item][fc[j]]['costs'] >= mC){
                    mC = model[item][fc[j]]['costs']
                }
                if(model[item][fc[j]]['solarArea'] >= mSA){
                    mSA = model[item][fc[j]]['solarArea']
                }
                if(model[item][fc[j]]['windArea'] >= mWA){
                    mWA = model[item][fc[j]]['windArea']
                }
            }
            obj.mE += mE
            obj.mC += mC
            obj.mSA += mSA
            obj.mWA += mWA    
        }
        //End
        //Saving model object
        obj.model = model
        //End
        var I = []
        for(let i = 0 ; i < n ; i++ ){
            I.push(obj.generateRandomSolution(obj.model))
        }
        obj.fastNonDominatedSort(I)
        //Emit progress event
        subject.next({gen:0,front: I,progress: 0})
        //End
        I.sort((a,b)=>{
            return (a.rank - b.rank)
        })
        let P = []
        P.push(I[0])
        for(let i = 1 ; i < I.length; i++ ){
            if(I[i].rank != I[i-1].rank){
                obj.crowdingDistanceAssignment(P)
                P = []
            }
            P.push(I[i])
        }
        obj.crowdingDistanceAssignment(P)
        var Q = obj.makeNewPop(I)
        for( let i = 0 ; i < gen ; i++ ){
            let I0 = []
            for(let j = 0 ; j < I.length; j++ ){
                I0.push(I[j])
            }
            let R = I.concat(Q)
            obj.fastNonDominatedSort(R)
            R.sort((a,b)=>{
                return (a.rank - b.rank)
            })
            let P = []
            P.push(R[0])
            for(let j = 1 ; j < R.length; j++ ){
                if(R[j].rank != R[j-1].rank){
                    obj.crowdingDistanceAssignment(P)
                    P = []
                }
                P.push(R[j])
            }
            obj.crowdingDistanceAssignment(P)
            R.sort((a,b)=>{
                if(obj.betterByFitness(a,b))return -1
                else return 1
            })
            I = []
            for(let j = 0 ; j < n ; j++ ){
                I.push(R[j])
            }
            Q = obj.makeNewPop(I)
            //Emit progress event
            subject.next({gen:i+1,front: I,progress: ((i+1)/gen) })
            //End
        }
        return I;
    },
}
expose(obj)