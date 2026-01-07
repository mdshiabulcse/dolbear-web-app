<template>
  <div class="sg-page-content">
    <section class="contact-section">
      <div class="container">
        <div class="page-title">
          <h1>Stores</h1>
        </div>
        <div v-if="stores.length > 0" class="contact-content">
          <div class="row" v-for="store in stores" :key="store.id">
            <template>
              <div class="col-md-6">
                <div class="title b-0">
                  <!-- <h1>Store In Map</h1> -->
                </div>
                <div class="form-group">
                  <label v-if="store.name">{{ store.name }}</label>
                  <div v-if="store.map" v-html="store.map"></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="contact-info">
                  <div class="title b-0">
                    <!-- <h1>{{ lang.reach_on_us }}</h1> -->
                  </div>
                  <div class="contact-list">
                    <ul class="global-list">
                      <li v-if="store.address">
                        <span class="mdi mdi-name mdi-map-marker"></span>
                        {{ store.address }}
                      </li>
                      <li v-if="store.phone">
                        <span class="mdi mdi-name mdi-phone"></span>
                        {{ store.phone }}
                      </li>
                      <li v-if="store.description">
                        <div v-html="store.description"></div>

                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </template>

            <hr />
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import shimmer from "../partials/shimmer";

export default {
  name: "contact",
  components: {
    shimmer,
  },
  mounted() {
    this.getStores();
  },
  data() {
    return {
      loading: false,
      stores: [],
    };
  },

  computed: {
    contact() {
      return this.$store.getters.getContactPage;
    },
    shimmer() {
      return this.$store.state.module.shimmer;
    },
  },
  methods: {
    getStores() {
      this.loading = true;
      let url = this.getUrl("store/allStore");
      axios
        .get(url)
        .then((response) => {
          if (response.data) {
            this.stores = response.data;
          } else {
            toastr.error(response.data.error, this.lang.Error + " !!");
          }

          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          if (error.response.status == 422) {
            this.errors = error.response.data.errors;
          }
        });
    },
  },
};
</script>