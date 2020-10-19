<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container pt-3">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="card">
                        <span v-if="first_inventory.status!='concluded'">
                            <div class="card-header">
                                <h3 class="card-title">Product Request</h3>
                            </div>

                            <form v-on:submit.prevent="submitRequest()" role="form">
                                <div class="card-body p-5">
                                    <div v-for="inventory in inventories" :key="inventory.id" style="border-top-width: 1px !important;"
                                        class="form-group"
                                    >
                                        <span class="col-md-5">
                                            <label>{{ inventory.product_name }}:</label>
                                        </span>
                                        <span class="col-md-5">
                                            <input type="number" name="quantity" class="form-control1" :id="inventory.id" :value="inventory.qty"
                                            />
                                        </span> 
                                    </div> 
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary btn-xs pull-right" >Approve </button>
                                </div>
                            </form>
                        </span>

                        <span v-else>
                            <div class="card-header">
                                <h3 class="card-title">Discharged Request</h3>
                            </div>

                            <form v-on:submit.prevent="submitRequest()" role="form">
                                <div class="card-body p-5">
                                    <div v-for="inventory in inventories" :key="inventory.id" style="border-top-width: 1px !important;"
                                        class="form-group"
                                    >
                                        <span class="col-md-5">
                                            <label>{{ inventory.product_name }}:</label>
                                        </span>
                                        <span class="col-md-5">
                                            <input type="number" name="quantity" class="form-control1" :id="inventory.id" :value="inventory.qty" readonly="true"
                                            />
                                        </span> 
                                    </div> 
                                </div>
                            </form>
                        </span>
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
            formData: {
                productQty: []
            },

            first_inventory: '',
            inventories: [],
            id: this.$route.params.reqId
        };
    },

    methods: {
        loadInventory() {
            if(this.is_busy) return;
            this.is_busy = true;
            axios.get("/api/fridge/discharge/" + this.id).then(({ data }) => {
                this.inventories = data.inventory;
                this.first_inventory = data.first_inventory;
            })
            .finally(() => {
            this.is_busy = false;
            });
        },


        submitRequest(event) {
            const qty = document.querySelectorAll('input[name="quantity"]');
            qty.forEach(element => {
                this.formData.productQty.push({
                    id: element.getAttribute("id"),
                    quantity: element.value
                });
            });

            axios.post("/api/fridge/inventory/update", this.formData)
                .then(()=>{
                        Swal.fire(
                            'Created!',
                            'You have approved this request.',
                            'success'
                        )
                        this.$router.push({path: '/bars/discharge'});
                    })

                .catch(error => {
                    Swal.fire(
                        "Failed!",
                        "There is error somewhere",
                        "error"
                    );
                });
        }
    },

    created() {
        this.loadInventory();
    }
};
</script>
<style>
.form-control1 {
    height: calc(2.25rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #ffffff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    box-shadow: inset 0 0 0 rgba(0, 0, 0, 0);
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
</style>
