<template>
    <ul>
        <li><router-link to="/">Home</router-link></li>
        <li><router-link to="/user">User</router-link></li>
    </ul>
</template>
<style>
    body{
        background-color:#ff0000;
    }
</style>
<script>
    export default{
        data(){
            return{
                msg:'hello vue'
            }
        }
    }
</script>
