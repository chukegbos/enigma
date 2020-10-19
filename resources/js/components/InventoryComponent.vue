<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Name or Code:" label-for="keyword">
                                <b-form-input id="name" v-model="filterForm.name" type="text"></b-form-input>
                            </b-form-group>

                            <b-form-group label="Category:">
                                <b-form-select
                                    v-model="filterForm.category"
                                    :options="categories"
                                    value-field="id"
                                    text-field="name"
                                >
                                    <template v-slot:first>
                                        <b-form-select-option :value="0">
                                            All
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-form-group>
                            <b-button type="submit" variant="primary">Search</b-button>
                        </b-form>
                    </b-modal>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Inventory Sections
                            </div>
                            <div class="card-tools">
                                <button class="btn btn-success" @click="newModal">
                                    Add Inventory
                                    <i class="fa fa-plus fa-fw"></i>
                                </button>
                            </div>
                        </div>
                        <div v-if="inventories.data.length">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <tr>
                                        <th>Code</th>
                                        <th>Category</th>
                                        <th>Name</th>
                                        <th>Unit</th>
                                        <th>Selling Price(&#8358;)</th>
                                        <th>Cost Price(&#8358;)</th>
                                        <th>Quantity</th>
                                        <th>Threshold</th>
                                        <th>Modify</th>
                                    </tr>

                                    <tr
                                        v-for="inventory in inventories.data"
                                        :key="inventory.id"
                                    >
                                        <td>{{ inventory.product_id }}</td>
                                        <td>{{ inventory.name }}</td>
                                        <td>{{ inventory.product_name }}</td>
                                        <td>{{ inventory.unit }}</td>
                                        <td>{{ inventory.price }}</td>
                                        <td>{{ inventory.cost_price }}</td>
                                        <td>{{ inventory.quantity }}</td>
                                        <td>{{ inventory.threshold }}</td>
                                        <!--<td>
                                            <button style="font-size: 5px" @click="addModal(inventory.id)">
                                                <i style="font-size: 2px; color: #38c172;" class="fa fa-plus"></i>
                                            </button>
                                            <span class="px-3">
                                                {{ inventory.quantity }}
                                            </span>

                                            <button style="font-size: 5px;" @click="subtractModal(inventory.id)">
                                                <i style="font-size: 1px; color: red;" class="fa fa-minus"></i>
                                            </button>
                                        </td>-->
                                        <td>
                                            <button class="btn btn-primary" @click.prevent="editModal(inventory)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger" @click.prevent="deleteInventory(inventory.id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <pagination :data="inventories" @pagination-change-page="getResults"></pagination>
                            </div>
                        </div>

                        <div v-else class="p-5">
                            <div class="alert alert-info text-center">
                                No Inventory Available.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="modal fade"
                id="addNewInventory"
                tabindex="-1"
                role="dialog"
                aria-labelledby="addNewInventoryLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewInventoryLabel"
                                v-show="!editMode"
                            >
                                Add New
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewInventoryLabel"
                                v-show="editMode"
                            >
                                Update Inventory Info
                            </h5>
                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                            >
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent=" editMode ? updateInventory() : createInventory()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <input v-model="form.product_name" type="text" name="product_name" class="form-control" :class="{'is-invalid': form.errors.has('product_name')}"/>
                                    <has-error :form="form" field="product_name"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Unit of Measurement</label>
                                    <input v-model="form.unit" type="text" name="unit" class="form-control" :class="{'is-invalid': form.errors.has('unit')}"/>
                                    <has-error :form="form" field="unit"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Selling Price</label>
                                    <input v-model="form.price" type="number" name="price" class="form-control":class="{'is-invalid': form.errors.has('price')}"/>
                                    <has-error :form="form" field="price"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Cost Price</label>
                                    <input v-model="form.cost_price" type="number" name="cost_price" class="form-control":class="{'is-invalid': form.errors.has('cost_price')}"/>
                                    <has-error :form="form" field="cost_price"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Threshold</label>
                                    <input v-model="form.threshold" type="number" class="form-control":class="{'is-invalid': form.errors.has('threshold')}"/>
                                    <has-error :form="form" field="threshold"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Select Category</label>
                                    <b-form-select
                                        v-model="form.category"
                                        :options="categories"
                                        value-field="id"
                                        text-field="name"
                                    >
                                    <template v-slot:first>
                                        <b-form-select-option :value="null" disabled>
                                            -- Please select category--
                                        </b-form-select-option>
                                    </template>
                                    </b-form-select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button v-show="editMode" type="submit" class="btn btn-success">
                                    Update
                                </button>
                                <button v-show="!editMode" type="submit" class="btn btn-primary">
                                    Create
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Adding Quantity Modal -->
            <div
                class="modal fade"
                id="addQuantityModal"
                tabindex="-1"
                role="dialog"
                aria-labelledby="addQuantityLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            Add Product Quantity
                        </div>
                        <div class="modal-body">
                            <form
                                @submit.prevent="
                                    addMode ? addQuantity() : subtractQuantity()
                                "
                            >
                                <div class="form-group">
                                    <input
                                        v-model="newQuantity"
                                        type="number"
                                        class="form-control"
                                        placeholder="New Quantity"
                                    />
                                </div>
                                <div style="display: flex;" class="">
                                    <button
                                        v-if="addMode"
                                        class="btn btn-success"
                                        type="submit"
                                    >
                                        Add
                                    </button>
                                    <button
                                        v-else
                                        class="btn btn-success"
                                        type="submit"
                                    >
                                        Subtract
                                    </button>
                                </div>
                            </form>
                        </div>
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
                editMode: false,
                addMode: false,
                model: {},
                singleQuantity: {},
                inventories: [],
                categories: [],
                user: "",

                form: new Form({
                    id: "",
                    product_name: "",
                    price: "",
                    category: null,
                    cost_price: "",
                    threshold: '',
                    unit: '',
                }),

                inventories: {
                    data: '',
                },

                filterForm: {
                    name: '',
                    category: 0,
                },

                quantityId: "",
                newQuantity: ""
            };
        },

        created() {
            this.loadInventory();
            this.getUser();
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                });
            },

            editModal(inventory) {
                (this.editMode = true), this.form.reset();
                $("#addNewInventory").modal("show");
                this.form.category = inventory.category;
                this.form.fill(inventory);
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewInventory").modal("show");
            },

            loadSingleQuantity(id) {
                axios.get("/api/inventory/quantity/" + id).then(({ data }) => {
                    this.singleQuantity = data;
                    console.log(this.singleQuantity);
                });
            },

            addModal(id) {
                this.quantityId = id;
                this.loadSingleQuantity(id);
                this.addMode = true;
                this.newQuantity = "";
                $("#addQuantityModal").modal("show");
            },

            subtractModal(id) {
                this.quantityId = id;
                this.loadSingleQuantity(id);
                this.addMode = false;
                this.newQuantity = "";
                $("#addQuantityModal").modal("show");
            },

            subtractQuantity() {
                let subtractedQuantity =
                    Number(this.singleQuantity) - Number(this.newQuantity);

                axios.post("/api/inventory/quantity/" + this.quantityId, {
                        quantity: subtractedQuantity
                    })
                    .then(() => {
                        this.loadInventory();
                        this.getUser();
                    })

                    .catch(err => {
                        console.log(err);
                    });

                $("#addQuantityModal").modal("hide");
            },

            addQuantity() {
                let addedQuantity =
                    Number(this.singleQuantity) + Number(this.newQuantity);

                axios
                    .post("/api/inventory/quantity/" + this.quantityId, {
                        quantity: addedQuantity
                    })
                    .then(() => {
                        this.loadInventory();
                        this.getUser();
                    })

                    .catch(err => {
                        console.log(err);
                    });
                $("#addQuantityModal").modal("hide");
            },

            getResults(page = 1) {
                axios.get("/api/inventory?page=" + page).then(response => {
                    this.inventories = response.data;
                });
            },

            loadInventory() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.filterForm.category = this.$route.params.id;
                axios.get("/api/inventory", { params: this.filterForm })
                .then(({ data }) => {
                    this.inventories = data.inventories;
                    this.categories = data.categories;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadInventory();
                this.getUser();
                this.$refs.filter.hide();
            },

            createInventory() {
                axios.post("/api/inventory", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Inventory Created Successfully.",
                        "success"
                    );
                    $("#addNewInventory").modal("hide");
                    this.loadInventory();
                    this.getUser();
                })

                .catch(error => {
                    console.log(error.response.data.error);
                    if (
                        error.response.data.error == "Inventory Already Exist"
                    ) {
                        Swal.fire(
                            "Failed!",
                            "Inventory Already Exist",
                            "error"
                        );
                    }
                });
            },

            updateInventory() {
                this.form.put("/api/inventory/" + this.form.id)
                .then(() => {
                    Swal.fire(
                        "Updated!",
                        "Inventory Updated Successfully.",
                        "success"
                    );
                    $("#addNewInventory").modal("hide");
                    this.loadInventory();
                    this.getUser();
                })

                .catch(() => {
                    this.$Progress.fail();
                });
            },

            deleteInventory(id) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                })
                .then(result => {
                    if (result.value) {
                        this.$Progress.start();
                        this.form.delete("/api/inventory/" + id)
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Your file has been deleted.",
                                "success"
                            );
                            this.$Progress.finish();
                            this.loadInventory();
                            this.getUser();
                        })

                        .catch(() => {
                            Swal(
                                "Failed!",
                                "Ops, Something went wrong, try again.",
                                "Warning"
                            );
                        });
                    }
                });
            }
        },
    };
</script>
