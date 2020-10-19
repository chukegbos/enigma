<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Add Purchase</h3>
                        </div>

                        <form v-on:submit.prevent="submitRequest()" role="form">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="input-live">Product:</label>
                                    <b-form-select v-model="form.product_id" :options="inventories" value-field="id" text-field="product_name" label="Select Product">
                                        <template v-slot:first>
                                            <b-form-select-option :value="null" disabled- Please select product---</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>


                                <div class="form-group">
                                    <label>Total Price in Yuan:</label>
                                    <b-form-input v-model="form.cost_price_yuan" type="number" @change="currency()"></b-form-input>
                                </div>

                                <div class="form-group">
                                    <label>Total Price in Naira:</label>
                                    <b-form-input v-model="form.cost_price" type="number" readonly></b-form-input>
                                </div>

                                <div class="form-group">
                                    <label>Quantity:</label>
                                    <b-form-input v-model="form.quantity" type="number" @change="calculate()"></b-form-input>
                                </div>

                                <div class="form-group">
                                    <label>Date of Purchase:</label>
                                    <b-form-input v-model="form.purchase_date" type="date"></b-form-input>
                                </div>

                                <div class="form-group">
                                    <label>Unit Cost Price:</label>
                                    <b-form-input v-model="form.unit_cost_price" type="number" readonly></b-form-input>
                                </div>

                                <div class="form-group">
                                    <label>Unit Selling Price:</label>
                                    <b-form-input v-model="form.unit_selling_price" type="number"></b-form-input>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    Submit
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
   
    export default {
        
        data() {
            return {
                is_busy: false,
                nairaSign: "&#x20A6;",
              
                form: {
                    product_id: '',
                    quantity: '',
                    cost_price: '',
                    purchase_date: '',
                    unit_selling_price: '',
                    unit_cost_price: '',
                    cost_price_yuan: '',
                },
                site: '',
                inventories: []
            };
        },

        created() {
            this.loadInventory();
            this.loadSite();
        },

        methods: {
            loadInventory() {
                axios.get("/api/inventory")
                .then(({ data }) => {
                    this.inventories = data.data;
                });
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },


            submitRequest(event) {
                axios.post("/api/purchases", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Your request has been noted.",
                        "success"
                    );
                    this.form = '';
                    this.loadSite();
                    this.loadInventory();
                })

                .catch(error => {
                    Swal.fire(
                        "Failed!",
                        "There is error somewhere",
                        "error"
                    );
                    this.form = '';
                    this.loadSite();
                    this.loadInventory();
                });
            },

            calculate() {
                this.form.unit_cost_price = this.form.cost_price/this.form.quantity;
            },

            currency() {
                this.form.cost_price = this.site.naira_value * this.form.cost_price_yuan;
            },
        },
    };
</script>
