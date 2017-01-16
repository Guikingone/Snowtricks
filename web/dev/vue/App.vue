<template>
  <div>
    <p><router-link to="/user">User</router-link></p>
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  data() {
    return {
      users: []
    }
  },
  methods: {
    fetchData() {
      this.$http.get('http://127.0.0.1:8000/api/tricks/all')
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