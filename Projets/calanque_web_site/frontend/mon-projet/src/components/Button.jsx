import "../styles/Button.css";

const Button = ({ 
  children, 
  type = "button", 
  variant = "primary", 
  size = "medium", 
  disabled = false, 
  loading = false,
  onClick,
  className = "",
  ...props 
}) => {
  const buttonClasses = [
    "btn",
    `btn-${variant}`,
    `btn-${size}`,
    className
  ].filter(Boolean).join(" ");

  return (
    <button
      type={type}
      className={buttonClasses}
      disabled={disabled || loading}
      onClick={onClick}
      {...props}
    >
      {loading ? (
        <>
          <span className="btn-spinner"></span>
          Chargement...
        </>
      ) : (
        children
      )}
    </button>
  );
};

export default Button;
