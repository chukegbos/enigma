<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                All Request
                            </div>
                        </div>

                        <div v-if="reqs.data.length" style="text-align:center">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Store</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Requested By</th>
                                        <th>Approved By</th>
                                        <th>Recieved By</th>
                                        <th>Status</th>
                                    </tr>

                                    <tr v-for="req in reqs.data" :key="req.id">
                                        <td>{{ req.req_id }}</td>
                                        <td>{{ req.store_name }}</td>
                                        <td>{{ req.product_name }}</td>
                                        <td>{{ req.qty }}</td>
                                        <td>
                                            {{ req.sender_id }} <br> {{ req.created_at | myDate }}
                                        </td>
                                        <td>
                                            <a href="#" @click="getItems(req)" v-if="!req.approved_by && !req.reciever_id && req.status != 'declined'">
                                                Approve
                                            </a>
                                            <span v-else-if="req.status == 'declined'"><i class="fa fa-times text-red"></i></span>
                                            <span v-else>{{ req.approved_by }}</span>
                                        </td>
                                        <td>
                                            <span v-if="req.status == 'declined'"><i class="fa fa-times text-red"></i></span>
                                            <span v-else>{{ req.reciever_id }}</span>
                                        </td>
                                        <td>
                                            <span v-if="req.status == 'approved'" class="badge badge-pills badge-primary">{{ req.status | capitalize }}</span>
                                            <span v-if="req.status == 'concluded'" class="badge badge-pills badge-success">{{ req.status | capitalize }}</span>
                                            <span v-if="req.status == 'pending'" class="badge badge-pills badge-warning">{{ req.status | capitalize }}</span>
                                            <span v-if="req.status == 'declined'" class="badge badge-pills badge-danger">{{ req.status | capitalize }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <pagination :data="reqs" @pagination-change-page="getResults"></pagination>
                            </div>
                        </div>

                        <div v-else class="p-5">
                            <div class="alert alert-info text-center">
                                No request found.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="getItem" tabindex="-1" role="dialog" aria-labelledby="addNewFridgeLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"id="addNewstoreLabel">
                                Edit Quantity 
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="confirm">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Store</label>
                                    <input v-model="form.store" type="text" readonly="true" class="form-control"/>
                                </div>
                            
                                <div class="form-group">
                                    <label>Product</label>
                                    <input v-model="form.product" type="text" readonly="true" class="form-control"/>
                                </div>
                          
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input v-model="form.qty" type="number" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label>Available in Warehouse</label>
                                    <input v-model="form.quantity" type="number" readonly="true" class="form-control"/>
                                    <input v-model="form.id" type="hidden"/>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-warning" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-success">
                                    Approve
                                </button>
                                <a href="#" class="btn btn-danger" @click=decline(form.id)>
                                    Declined
                                </a>
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
    created() {
        this.loadInventory();
    },

    data() {
        return {
            is_busy: false,
            reqs: [],
            form: new Form({
                id: "",
                qty: "",
                quantity: "",
                store: "",
                product: "",
            })
        };
    },

    methods: {
        loadInventory() {
            if(this.is_busy) return;
            this.is_busy = true;

            axios.get("/api/admin/discharge")
            .then(({ data }) => {
                this.reqs = data;
            }) 
            .finally(() => {
                this.is_busy = false;
            });
        },

        getResults(page = 1) {
            axios.get("/api/admin/discharge?page=" + page)
            .then(response => {
                this.reqs = response.data;
            });
        },

        getItems(req) {
            this.form.id = req.id;
            this.form.quantity = req.quantity;
            this.form.qty = req.qty;
            this.form.store = req.store_name;
            this.form.product = req.product_name;
            $("#getItem").modal("show");
        },

        confirm() {
            axios.put("/api/admin/discharge/" + this.form.id, this.form)
            .then((data) => {
                console.log(data)
                if(data.data.error=="You cannot disburse more than you dont have in your warehouse"){
                    Swal.fire(
                        "Failed!",
                        "You cannot disburse more that you dont have in your warehouse",
                        "error"
                    );
                }
                else
                {
                    Swal.fire(
                        "Created!",
                        "Request Approved Successfully.",
                        "success"
                    );
                }

                $("#getItem").modal("hide");
                this.loadInventory();
            })
            .catch(error => {
                Swal.fire(
                    "Failed!",
                    "There is an error somewhere",
                    "error"
                );
            });
        },

        decline(id) {
            axios.get("/api/admin/decline/" + id)
            .then(() => {
                Swal.fire(
                    "Created!",
                    "Request Rejected.",
                    "success"
                );
                $("#getItem").modal("hide");
                this.loadInventory();
            })
            .catch(error => {
                Swal.fire(
                    "Failed!",
                    "There is an error somewhere",
                    "error"
                );
            });
        },
    }
};
</script>

<style>
.list_product {
    list-style: none;
}
</style>
