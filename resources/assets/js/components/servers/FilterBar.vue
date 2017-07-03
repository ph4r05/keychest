<template>
    <div class="filter-bar">
      <form class="form-inline">
        <div class="form-group">
          <label for="server-search-inp">Filter results:</label>
          <input type="text" v-model="filterText" class="form-control input-sm"
                 @keyup.enter="doFilter" placeholder="server name" id="server-search-inp">

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

@media (min-width: 768px) {
    .filter-bar {
        margin-bottom: 10px;
    }
}

@media (max-width: 768px) {
    .filter-bar .form-inline .form-group input[type=text] {
        margin-bottom: 5px;
    }
}

</style>
