document.getElementById("loginBtn").addEventListener("click", async () => {
    const user = document.getElementById("user").value;
    const password = document.getElementById("password").value;

    try {
        const res = await fetch(`${BASE_URL}/api/auth/login`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ user, password })
        });

        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.acceso);
        }

        const data = await res.json();
        localStorage.setItem("token", data.token);
        localStorage.setItem("user", data.user);
        localStorage.setItem("nombre_apellidos", data.nombre_apellidos);
        window.location.href = `${BASE_URL}/`;
    } catch (err) {
        document.getElementById("error").innerText = err.message;
    }
});