evalExpr(expr, vars, vals) = {
    my(res = expr);

    for(i = 1, length(vars),
        res = subst(res, vars[i], vals[i]);
    );

    return(simplif(res)); \\ Nettoie l'expression pour le calcul numérique
}

\\ Lecture matrice clavier
lireMatrice(n, nom) = {
    my(M);

    print();
    print("Entrer la matrice ", nom);
    print("Format : [a,b,c;d,e,f;g,h,i]");

    M = eval(input());

    if(matsize(M)[1] != n || matsize(M)[2] != n,
        error("Dimension incorrecte")
    );

    return(M);
}

\\ Lecture vecteur
lireVecteur(n)=
{
    my(X);

    print();
    print("Entrer x0");
    print("Format : [x1,x2,...,xn]");

    X=eval(input());

    if(
        length(X)!=n,

        error(
            "Dimension du vecteur incorrecte"
        )
    );

    return(X);
}

\\ Newton
recherche_point_singulier() = {
    my(
        n, A, B, x0, vars, f, grad, H, 
        grad_num, H_num, pas, iter, tol, 
        maxiter, eig, is_min, is_max
    );

    print("================");
    print("Dimension n ?");
    n = input();

    A = lireMatrice(n, "A");
    B = lireMatrice(n, "B");
    x0 = lireVecteur(n);

    \\ CRUCIAL : Forcer x0 en nombres réels (flottants) pour éviter les fractions géantes
    x0 = x0 * 1.0;

    vars = vector(n, i, eval(Str("x", i)));

    f = (vars * A * vars~)^2 - (vars * B * vars~);

    print("----------------");
    print("Fonction :");
    print(f);

    grad = vector(n, i, deriv(f, vars[i]));

    print("----------------");
    print("Gradient :");
    print(grad);

    H = matrix(n, n, i, j, deriv(grad[i], vars[j]));

    print("----------------");
    print("Hessienne :");
    print(H);

    tol = 1E-8;
    maxiter = 100;
    iter = 0;

    print("----------------");
    print("Calcul de Newton en cours...");

    while(iter < maxiter,
        grad_num = evalExpr(grad, vars, x0);
        H_num = evalExpr(H, vars, x0);

        \\ norml2 renvoie la somme des carrés, on prend la racine pour la vraie norme
        if(sqrt(norml2(grad_num)) < tol,
            break
        );

        if(matdet(H_num) == 0,
            error("Hessienne singuliere")
        );

        pas = matsolve(H_num, -grad_num~);
        x0 = x0 + pas~;
        iter++;
    );

    print();
    print("----------------");
    print("Point critique (obtenu en ", iter, " iterations) :");
    print(x0);

    H_num = evalExpr(H, vars, x0);
    eig = polroots(charpoly(H_num));

    print();
    print("Valeurs propres :");
    print(eig);

    is_min = 1;
    is_max = 1;

    for(i = 1, length(eig),
        if(real(eig[i]) <= 0,
            is_min = 0
        );
        if(real(eig[i]) >= 0,
            is_max = 0
        );
    );

    print();
    if(is_min,
        print("C'est un minimum local")
    );
    if(is_max,
        print("C'est un maximum local")
    );
    if(!is_min && !is_max,
        print("C'est un point selle")
    );

    return(x0);
}

\\ EXECUTION
recherche_point_singulier();

