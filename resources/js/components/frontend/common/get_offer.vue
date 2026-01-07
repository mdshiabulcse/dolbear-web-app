<template lang="">
    <div class="container mb-5 mt-5">
        <div class="card_new d-flex flex-column flex-md-row justify-content-center">
            <h4>Get Back 5% Offer On Your First Order</h4>
            <input v-model="email" type="email" placeholder="your email address" class="offer-input">
            <button class="offer-btn" @click="submit">Get Discount</button>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            email: "",
        }
    },

    methods: {
        submit() {

            if (!this.email) {
                return toastr.warning("Please Enter Email", "Warning !!");
            }

            axios
                .post("/subscribers/save", {
                    email: this.email
                })
                .then((response) => {
                    if (response.data.success) {
                        toastr.success("Subscription successful.");
                        this.email = "";
                    } else {
                        toastr.error(response.data.error, "Error !!");
                        this.email = "";
                    }
                });
        }
    }
}
</script>
<style scoped>
.card_new {
    padding: 20px 65px;
}

.card_new h4 {
    margin-top: 3px;
}

.offer-input {
    width: 380px;
    margin: 0px 30px;
    border-radius: 5px;
    border: 1px solid #9FDBFF;
    box-shadow: 0px 3px 3px 0px #00000029;
    text-align: center;
}

.offer-btn {
    padding: 3px 30px;
    border-radius: 5px;
    background: #6CC9F0;
    color: white;
    border: none;
    box-shadow: 0px 3px 3px 0px #00000029;
    text-align: center;

}

@media screen and (max-width: 768px) {
    .offer-input {
        width: 100%;
        margin: 15px 0px; 
    }
}
</style>