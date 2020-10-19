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
                                    <label>Quantity:</label>
                                    <b-form-input v-model="form.quantity" type="number"></b-form-input>
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
                },
                inventories: []
            };
        },

        created() {
            this.loadInventory();
        },

        methods: {
            loadInventory() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/inventory")
                .then(({ data }) => {
                    this.inventories = data.data;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            submitRequest(event) {
                axios.post("/api/store/request", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Your request has been noted.",
                        "success"
                    );
                    this.form = '';
                    this.loadInventory();
                })

                .catch(error => {
                    Swal.fire(
                        "Failed!",
                        "There is error somewhere",
                        "error"
                    );
                    this.form = '';
                    this.loadInventory();
                });
            },
        },
    };
</script>
