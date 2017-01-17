<template>
    <div>
        <p>Hey !</p>
        <p>{{ msg }}</p>
        <label>
            <input type="text" name="username" v-model="users.username" />
            <input type="email" name="email" v-model="users.email" />
            <input type="password" name="password" v-model="users.password" />
            <input type="hidden" name="password-repeat" v-model="users.password_second" />
        </label>
        <button @click.prevent="onSubmit">
            Envoyer !
        </button>
        <div v-if="isSubmitted">
            <p>You just type : {{ email }}</p>
            <p>You just type : {{ username }}</p>
            <p>You just type : {{ password }}</p>
            <p>You just type : {{ password_repeat }}</p>
        </div>
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
                users: {
                    username: '',
                    email: '',
                    password: ''
                },
                msg:'hello vue',
            }
        },
        methods: {
            onSubmit() {
                this.isSubmitted = true,
                this.$http.post('http://127.0.0.1:8000/api/user/register', this.users)
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
