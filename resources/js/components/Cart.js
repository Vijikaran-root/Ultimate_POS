import React, { Component } from "react";
import { createRoot } from "react-dom";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            customers: [],
            barcode: "",
            search: "",
            customer_id: "",
            translations: {},
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setCustomerId = this.setCustomerId.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadCart();
        this.loadProducts();
        this.loadCustomers();
    }

    // load the transaltions for the react component
    loadTranslations() {
        axios
            .get("/admin/locale/cart")
            .then((res) => {
                const translations = res.data;
                this.setState({ translations });
            })
            .catch((error) => {
                console.error("Error loading translations:", error);
            });
    }

    loadCustomers() {
        axios.get(`/admin/customers`).then((res) => {
            const customers = res.data;
            this.setState({ customers });
        });
    }

    loadProducts(search = "") {
        const query = !!search ? `?search=${search}` : "";
        axios.get(`/admin/products${query}`).then((res) => {
            const products = res.data.data;
            this.setState({ products });
        });
    }

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        console.log(barcode);
        this.setState({ barcode });
    }

    loadCart() {
        axios.get("/admin/cart").then((res) => {
            const cart = res.data;
            this.setState({ cart });
        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode } = this.state;
        if (!!barcode) {
            axios
                .post("/admin/cart", { barcode })
                .then((res) => {
                    this.loadCart();
                    this.setState({ barcode: "" });
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }
    handleChangeQty(product_id, qty) {
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.quantity = qty;
            }
            return c;
        });

        this.setState({ cart });
        if (!qty) return;

        axios
            .post("/admin/cart/change-qty", { product_id, quantity: qty })
            .then((res) => {})
            .catch((err) => {
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    getTotal(cart) {
        const total = cart.map((c) => c.pivot.quantity * c.price);
        return sum(total).toFixed(2);
    }
    getMRPTotal(cart) {
        const total = cart.map((c) => c.pivot.quantity * c.cost);
        return sum(total).toFixed(2);
    }
    handleClickDelete(product_id) {
        axios
            .post("/admin/cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                this.setState({ cart });
            });
    }
    handleEmptyCart() {
        axios.post("/admin/cart/empty", { _method: "DELETE" }).then((res) => {
            this.setState({ cart: [] });
        });
    }
    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });
    }
    handleSeach(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    addProductToCart(barcode) {
        let product = this.state.products.find((p) => p.barcode === barcode);
        if (!!product) {
            // if product is already in cart
            let cart = this.state.cart.find((c) => c.id === product.id);
            if (!!cart) {
                // update quantity
                this.setState({
                    cart: this.state.cart.map((c) => {
                        if (
                            c.id === product.id &&
                            product.quantity > c.pivot.quantity
                        ) {
                            c.pivot.quantity = c.pivot.quantity + 1;
                        }
                        return c;
                    }),
                });
            } else {
                if (product.quantity > 0) {
                    product = {
                        ...product,
                        pivot: {
                            quantity: 1,
                            product_id: product.id,
                            user_id: 1,
                        },
                    };

                    this.setState({ cart: [...this.state.cart, product] });
                }
            }

            axios
                .post("/admin/cart", { barcode })
                .then((res) => {
                    // this.loadCart();
                    console.log(res);
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }
    setCustomerId(event) {
        this.setState({ customer_id: event.target.value });
    }
    handleClickSubmit() {
        const total = this.getTotal(this.state.cart); // Get the total amount from the cart
        Swal.fire({
            title: "Received Amount",
            text: `Bill Total: ${total}`,
            input: "text",
            inputPlaceholder: "Enter the received amount",
            inputValidator: (value) => {
                if (!value || isNaN(value) || value <= 0) {
                    return "Please enter a valid amount!";
                }
            },
            cancelButtonText: "Cancel Pay",
            showCancelButton: true,
            confirmButtonText: "Confirm Pay",
            showLoaderOnConfirm: true,
            preConfirm: (receivedAmount) => {
                receivedAmount = parseFloat(receivedAmount);
                const balance = receivedAmount - total;

                if (balance < 0) {
                    Swal.showValidationMessage("Insufficient received amount!");
                    return false;
                }

                return axios
                    .post("/admin/orders", {
                        customer_id: this.state.customer_id,
                        amount: receivedAmount,
                    })
                    .then((res) => {
                        this.loadCart();
                        return { ...res.data, balance }; // Pass balance in the result
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(err.response.data.message);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    title: "Order Placed!",
                    text: `Balance: ${result.value.balance}`,
                    icon: "success",
                });
            }
        });
    }
    render() {
        const { cart, products, customers, barcode, translations } = this.state;
        return (
            <div className="container-fluid h-full">
                <div className="row h-full">
                    {/* Left Panel */}
                    <div className="col-md-5 d-flex flex-column">
                        {/* Customer and Barcode */}
                        <div className="mb-3">
                            <form
                                onSubmit={this.handleScanBarcode}
                                className="mb-2"
                            >
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder="Scan Barcode"
                                    value={barcode}
                                    onChange={this.handleOnChangeBarcode}
                                />
                            </form>
                            <select
                                className="form-control"
                                onChange={this.setCustomerId}
                            >
                                <option value="" disabled selected>
                                    Select Customer
                                </option>
                                {customers.map((cus) => (
                                    <option key={cus.id} value={cus.id}>
                                        {`${cus.first_name} ${cus.last_name}`}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Cart Section */}
                        <div className="user-cart d-flex flex-column h-screen overflow-hidden">
                            <div className="card flex-grow-1 shadow-sm overflow-hidden">
                                <div className="card-header bg-primary text-white">
                                    <h5 className="mb-0">Cart</h5>
                                </div>
                                <div
                                    className="table-responsive overflow-auto"
                                    style={{ maxHeight: "400px" }}
                                >
                                    <table className="table mb-0 table-striped">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>MRP</th>
                                                <th className="text-end">
                                                    Our Price
                                                </th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {cart.map((c) => (
                                                <tr key={c.id}>
                                                    <td>{c.name}</td>
                                                    <td className="d-flex align-items-center">
                                                        <input
                                                            type="number"
                                                            className="form-control form-control-sm me-2"
                                                            style={{
                                                                width: "80px",
                                                            }}
                                                            value={
                                                                c.pivot.quantity
                                                            }
                                                            min="1"
                                                            onChange={(event) =>
                                                                this.handleChangeQty(
                                                                    c.id,
                                                                    event.target
                                                                        .value
                                                                )
                                                            }
                                                        />
                                                        <button
                                                            className="btn btn-danger btn-sm"
                                                            onClick={() =>
                                                                this.handleClickDelete(
                                                                    c.id
                                                                )
                                                            }
                                                        >
                                                            <i className="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        {
                                                            window.APP
                                                                .currency_symbol
                                                        }{" "}
                                                        {c.cost}
                                                    </td>
                                                    <td>
                                                        {
                                                            window.APP
                                                                .currency_symbol
                                                        }{" "}
                                                        {c.price}
                                                    </td>
                                                    <td className="text-end">
                                                        {
                                                            window.APP
                                                                .currency_symbol
                                                        }{" "}
                                                        {(
                                                            c.price *
                                                            c.pivot.quantity
                                                        ).toFixed(2)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            {/* Total and Actions */}
                            <div className="p-3">
                                <div className="d-flex justify-content-between mb-2 text-green">
                                    <h5>Total Discount:</h5>
                                    <h5>
                                        {window.APP.currency_symbol}{" "}
                                        {this.getMRPTotal(cart) -
                                            this.getTotal(cart)}
                                    </h5>
                                </div>
                                <div className="d-flex justify-content-between mb-2">
                                    <h5>Total:</h5>
                                    <h5>
                                        {window.APP.currency_symbol}{" "}
                                        {this.getTotal(cart)}
                                    </h5>
                                </div>
                                <div className="d-flex">
                                    <button
                                        type="button"
                                        className="btn btn-danger flex-grow-1 m-1"
                                        onClick={this.handleEmptyCart}
                                        disabled={!cart.length}
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="button"
                                        className="btn btn-primary flex-grow-1 m-1"
                                        onClick={this.handleClickSubmit}
                                        disabled={!cart.length}
                                    >
                                        Checkout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right Panel */}
                    <div className="col-md-7">
                        {/* Search */}
                        <div className="mb-3">
                            <input
                                type="text"
                                className="form-control"
                                placeholder="Search Products..."
                                onChange={this.handleChangeSearch}
                                onKeyDown={this.handleSeach}
                            />
                        </div>

                        {/* Products */}
                        <div className="order-product d-flex flex-wrap gap-3">
                            {products.map((p) => (
                                <div
                                    onClick={() =>
                                        this.addProductToCart(p.barcode)
                                    }
                                    key={p.id}
                                    className="item card shadow-sm text-center p-2"
                                    style={{
                                        cursor: "pointer",
                                        width: "150px",
                                    }}
                                >
                                    <img
                                        src={p.image_url}
                                        alt={p.name}
                                        className="img-fluid mb-2"
                                        style={{
                                            height: "100px",
                                            objectFit: "cover",
                                        }}
                                    />
                                    <h6
                                        className="mb-1"
                                        style={
                                            window.APP.warning_quantity >
                                            p.quantity
                                                ? { color: "red" }
                                                : {}
                                        }
                                    >
                                        {p.name} ({p.quantity})
                                    </h6>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default Cart;

const root = document.getElementById("cart");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Cart />);
}
