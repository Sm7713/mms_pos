<template>
    <div>
        Hello my name is salim, hello my freind.
        <button @click="listget()">click</button>
        <p v-for="(item, index) in items" :key="index">{{item.max_amount}}</p>
        <p>1</p>
    </div>
</template>
<script>
export default{
    // name: 'App',
    data() {
        return {
            items:[]
        }
    },methods:{
        listget(){
            axios.get('api/admin/sellpoint').then(response=>{
                this.items=response.data.data
            })
        },

    }
}
</script>