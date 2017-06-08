<template>
    <div class="filter-bar">
      <form class="form-inline">
        <div class="form-group">
          <label>Search for:</label>
          <input type="text" v-model="filterText" class="form-control input-sm"
                 @keyup.enter="doFilter" placeholder="server name">
          <button class="btn btn-sm btn-primary" @click.prevent="doFilter">Go</button>
          <button class="btn btn-sm" @click.prevent="resetFilter">Reset</button>
        </div>
      </form>
    </div>
</template>

<script>
  export default {
    data () {
      return {
        filterText: ''
      }
    },
    methods: {
      doFilter () {
        this.$events.fire('filter-set', this.filterText);
      },
      resetFilter () {
        this.filterText = '';
        this.$events.fire('filter-reset');
      }
    }
  }
</script>
<style>
.filter-bar {
  padding: 10px;
  float: left;
}
</style>
