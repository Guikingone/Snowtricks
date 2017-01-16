<template>
    <div>
        <p>Hey !</p>
        <p>{{ msg }}</p>
        <label>
            <input type="text" name="username" v-model="username" />
            <input type="email" name="email" v-model="email" />
            <input type="password" name="password" v-model="password" />
            <input type="password" name="password-repeat" v-model="password_repeat" />
        </label>
        <button @click="onSubmit">
            Envoyer !
        </button>
        <p>You just type : {{ email }}</p>
        <p>You just type : {{ username }}</p>
        <p>You just type : {{ password }}</p>
        <p>You just type : {{ password_repeat }}</p>
    </div>
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
                msg:'hello vue',
                username: '',
                email: '',
                password: '',
                password_repeat: ''
            }
        },
        methods: {
            onSubmit() {
                this.$http.get('http://127.0.0.1:8000/api/user/register')
                  .then(response => {
                    return response.json();
                  })
                  .then(data => {
                    const resultArray = [];
                    for (let key in data) {
                      resultArray.push(data[key])
                    }
                    this.users = resultArray;
                    console.log(this.users);
                  })
            }
        }
    }
</script>
