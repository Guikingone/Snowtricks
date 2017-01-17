<template>
  <div>
      <label for="register_email" class="required">Email</label>
      <input type="email" name="register[email]" id="register_email" required="required" />
      <button type="submit">Envoyer !</button>
  </div>
</template>

<script>
import Header from './components/Header.vue';
export default {
    components: {
        appHeader: Header
    },
    data() {
      return {
        users: []
      }
    },
    methods: {
      fetchData() {
        this.$http.get('http://127.0.0.1:8000/api/users/all')
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