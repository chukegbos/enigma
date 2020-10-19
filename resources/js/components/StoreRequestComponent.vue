<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Products Request
                            </div>
                            <div class="card-tools">
                                <router-link
                                    to="/store/request/create"
                                    class="btn btn-success text-white"
                                >
                                    Make Request <i class="fa fa-plus fa-fw"></i>
                                </router-link>
                            </div>
                        </div>
                        <div v-if="reqs.data.length" style="text-align:center">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Requested By</th>
                                        <th>Approved By</th>
                                        <th>Recieved By</th>
                                        <th>Status</th>
                                    </tr>

                                    <tr v-for="req in reqs.data" :key="req.id">
                                        <td>{{ req.req_id }}</td>
                                        <td>{{ req.product_name }}</td>
                                        <td>{{ req.qty }}</td>
                                        <td>
                                            {{ req.sender_id }} <br> {{ req.created_at | myDate }}
                                        </td>
                                        <td>
                                            <span v-if="req.status == 'declined'"><i class="fa fa-times text-red"></i></span>
                                            <span v-else>{{ req.approved_by }}</span>
                                        </td>
                                        <td>
                                            <span v-if="req.status == 'declined'"><i class="fa fa-times text-red"></i></span>
                                            <a href="#" @click="getItems(req)" v-else-if="req.approved_by && !req.reciever_id && req.status != 'declined'">
                                                Accept
                                            </a>
                                            <span v-else>{{ req.reciever_id }}</span>
                                        </td>
                                        <td>
                                            <span v-if="req.status == 'approved'" class="badge badge-pills badge-success">{{ req.status | capitalize }}</span>
                                            <span v-if="req.status == 'concluded'" class="badge badge-pills badge-primary">{{ req.status | capitalize }}</span>
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
                                No Request Available.
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
                       
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Product</label>
                                <input v-model="form.product" type="text" readonly="true" class="form-control"/>
                            </div>
                      
                            <div class="form-group">
                                <label>Quantity</label>
                                <input v-model="form.qty" type="number" readonly="true" class="form-control"/>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-warning" data-dismiss="modal">
                                Close
                            </button>
                            <a href="#" class="btn btn-success" @click=accept(form.id)>
                                Accept
                            </a>
                        </div>
                        
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
            reqs: [],
            is_busy: false,
            form: new Form({
                id: "",
                qty: "",
                product: "",
            })
        };
    },

    methods: {
        loadInventory() {
            if(this.is_busy) return;
            this.is_busy = true;

            axios.get("/api/store/request")
            .then(({ data }) => {
                this.reqs = data;
                console.log(this.reqs)
            })
            .finally(() => {
                this.is_busy = false;
            });
        },

        getItems(req) {
            this.form.id = req.id;
            this.form.qty = req.qty;
            this.form.product = req.product_name;
            $("#getItem").modal("show");
        },

        getResults(page = 1) {
            axios.get("api/store/request?page=" + page)
            .then(response => {
                this.reqs = response.data;
            });
        },

        accept(id) {
            axios.get("/api/store/accept/" + id)
            .then(() => {
                Swal.fire(
                    "Created!",
                    "Product Accepted.",
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
