<template>
  <div class="sg-page-content">
    <section class="sg-global-content">
      <div class="container">
          <div class="row">
          <user_sidebar :current="current"></user_sidebar>

              <div class="col-lg-9">
                <form @submit.prevent="saveAddress()">
                   <div class="row">
                    <h4 class="mb-3">Delivery Address</h4>
                     <div class="col-lg-6 b">
                       <div class="sg-card address border">
                         <div class="justify-content-between d-flex">
                           <div class="text">
                             <ul class="global-list">
                               <li>{{ lang.name }}: {{ form.name }}</li>
                               <li>{{ lang.email }}: {{ form.email || 'N/A' }}</li>
                               <li>{{ lang.phone }}: {{ form.phone_no }}</li>
                               <li>Division: {{ form.division ?? 'N/A' }}</li>
                               <li>District: {{ form.district ?? 'N/A' }}</li>
                               <li>Thana: {{ form.thana ?? 'N/A' }}</li>
                               <li>Address: {{ form.address || 'N/A' }}</li>
                             </ul>
                           </div>

                           <div class="float-right">
                             <a @click="is_edit = !is_edit" style="cursor: pointer">{{ is_edit ? 'Cancel' : 'Edit' }}</a>
                           </div>

                         </div>
                       </div>
                     </div>
                     <div class="col-lg-8"></div>

                     <template v-if="is_edit">
                       <div class="col-lg-6">
                         <p>Full Name <span class="text-danger">*</span></p>
                         <input
                             type="text"
                             class="form-control mb-1 mb-md-3"
                             placeholder="Full Name"
                             v-model="form.name" required
                         >
                       </div>
                       <div class="col-lg-6">
                         <p>Phone Number <span class="text-danger">*</span></p>
                         <input
                             type="text"
                             class="form-control"
                             placeholder="Phone Number"
                             v-model="form.phone_no"
                             @input="sanitizePhoneNumber"
                             required
                         >
                       </div>
                       <div class="col-lg-6">
                         <p>Email <span class="text-danger">*</span></p>
                         <input
                             type="email"
                             class="form-control"
                             placeholder="Email"
                             v-model="form.email"
                             required
                         >
                       </div>

                       <div class="col-lg-6">
                         <div >
                           <p >Division  <span class="text-danger">*</span></p>
                           <v-select
                               label="name"
                               placeholder="Select a Division"
                               :options="divisions"
                               v-model="form.division_id"
                               :reduce="(option) => option.id"
                               @input="getStates()"
                               :class="{ 'error_border' : errors.division_id }"
                           >
                           </v-select>
                         </div>
                         <span class="validation_error" v-if="errors.division_id">{{ errors.division_id[0] }}</span>
                       </div>

                       <div class="col-md-6">
                         <div >
                           <p >District  <span class="text-danger">*</span></p>
                           <v-select
                               label="name"
                               placeholder="Select a District"
                               :options="states"
                               v-model="form.district_id"
                               :reduce="(option) => option.id"
                               @input="getCities()"
                               :class="{ 'error_border' : errors.district_id }"
                           >
                           </v-select>
                         </div>
                         <span class="validation_error" v-if="errors.district_id">{{ errors.state_id[0] }}</span>
                       </div>

                       <div class="col-md-6">
                         <div>
                           <p >Thana  <span class="text-danger">*</span></p>
                           <v-select
                               required
                               label="name"
                               placeholder="Select a Thana"
                               :options="cities"
                               v-model="form.thana_id"
                               :reduce="(option) => option.id"
                               :class="{ 'error_border' : errors.thana_id }"
                           >
                           </v-select>
                         </div>
                         <span class="validation_error"
                               v-if="errors.city_id">{{ errors.thana_id[0] }}</span>
                       </div>

                       <div class="col-md-12">
                         <div class="form-group">
                           <div class="form-group">
                             <label>Address</label>
                             <textarea required v-model="form.address"
                                       placeholder="Write your full address"
                                       class="form-control"
                                       :class="{ 'error_border' : errors.address }"
                             ></textarea>
                           </div>
                         </div>
                         <span class="validation_error"
                               v-if="errors.address">{{ errors.address[0] }}</span>
                       </div>

                       <div class="col-md-12">
                         <loading_button v-if="loading" :class_name="'btn btn-primary'"></loading_button>
                         <button type="submit" v-else class="btn btn-primary float-right">
                           Save
                         </button>
                       </div>
                     </template>

            </div>
                </form>
              </div>
        </div><!-- /.row -->
      </div><!-- /.container -->
    </section><!-- /.profile-section -->

  </div>
</template>

<script>
import user_sidebar from "../../partials/user_sidebar";
import shimmer from "../../partials/shimmer";
import addressForm from "../../partials/addressForm";
import {STORE_PICK} from "../../../../constants/deliveryMethod";

export default {
  name: "addresses",
  components: {
    user_sidebar, shimmer,addressForm
  },
  data() {
    return {
      current: 'addresses',
      default_shipping: this.$store.getters.getUser.billing_address,
      default_billing: this.$store.getters.getUser.shipping_address,
      is_edit: false,
      divisions: [],
      states: [],
      cities: [],
      loading: false,
      address_submit_loading: false,
      form: {
        name: '',
        phone_no: '',
        email: '',
        alternative_phone_no: '',
        address: '',
        country_id: '',
        division_id: '',
        district_id: '',
        thana_id: '',
        id: '',
        delivery_charge: 0,
      },
    }
  },
  mounted() {
    this.getUserAddress();
  },

  computed: {
    addresses() {
      return this.$store.getters.getUserAddresses
    },
    shimmer() {
      return this.$store.state.module.shimmer
    },
    flags() {
      return this.$store.getters.getFlags
    }
  },
  methods: {

    async saveAddress()
    {
      try{
        this.$Progress.start();

        if (!this.form.thana_id) {
          toastr.error('Invalid thana', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        if (!this.form.phone_no) {
          toastr.error('Empty Phone Number', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        if (!this.form.name) {
          toastr.error('Name is required', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(this.form.email)) {
          toastr.error('Please enter a valid email address.', this.lang.Error + " !!");
          this.$Progress.fail();
          return;
        }

        // Construct data to send
        const requestData = {
          form: this.form,
        };

        // API call
        const url = this.getUrl('user/delivery-address');
        const response = await axios.post(url, requestData);

        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$Progress.finish();
          toastr.success('Address updated successfully!', 'Success');
        }


      } catch(error) {
        this.$Progress.fail();
        toastr.error('Something went wrong. Please try again.', this.lang.Error + ' !!');
        console.error(error);
      }


    },

    async getUserAddress()
    {
      await this.getDivisions()

      let url = this.getUrl("user/delivery-address");
      axios.get(url).then(async (response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          const address = response.data
          this.form.name = address.name
          this.form.email = address.email
          this.form.address = address.address
          this.form.phone_no = address.phone_no
          this.form.division = address.division
          this.form.district = address.district
          this.form.thana = address.thana
          this.form.address = address.address

          if (address.address_ids?.division_id) {
            this.form.division_id = parseInt(address.address_ids?.division_id)
            await this.getStates()
          }

          if (address.address_ids?.district_id) {
            this.form.district_id = parseInt(address.address_ids?.district_id)
            await this.getCities()
          }

          if(address.address_ids?.thana_id){
            this.form.thana_id = parseInt(address.address_ids?.thana_id)
          }
        }
      });
    },
    getDivisions() {
      let url = this.getUrl('get/division-list/');
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {

          this.divisions = response.data.divisions;
        }
      });
    },
    getStates(address) {
      let division_id = this.form.division_id;

      let url = this.getUrl('state/by-country/' + division_id);
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.states = response.data.states;
          if (address && address.address_ids) {
            this.form.state_id = parseInt(address.address_ids.state_id);
            this.getCities(address);
          }
        }
      });

      this.form.district_id = "";
      this.form.thana_id = "";
    },
    getCities(address) {
      let state_id = this.form.district_id;

      let url = this.getUrl('city/by-state/' + state_id);
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.cities = response.data.cities;
          if (address && address.address_ids) {
            this.form.city_id = parseInt(address.address_ids.city_id);
          }
        }
      });

      this.form.thana_id = "";

    },
    sanitizePhoneNumber(event) {
      let input = event.target.value;

      // Remove all invalid characters (keep digits, spaces, and +)
      input = input.replace(/[^+\d\s]/g, '');

      // Ensure only one '+' at the beginning
      if (input.includes('+')) {
        input = '+' + input.replace(/\+/g, '').trim();
      }

      // Update the v-model value
      this.form.phone_no = input;
    },


    // makeDefault(id, type) {
    //   let url = this.getUrl('default/user-address/' + type + '/' + id);
    //   axios.post(url).then((response) => {
    //     if (response.data.error) {
    //       toastr.error(response.data.error, this.lang.Error + ' !!');
    //     } else {
    //       toastr.success(response.data.success, this.lang.Success + ' !!');
    //       this.$store.dispatch('user', response.data.user);
    //       this.default_shipping = response.data.user.shipping_address;
    //       this.default_billing = response.data.user.billing_address;
    //       this.getAddress();
    //     }
    //   })
    // },
  }
}
</script>
