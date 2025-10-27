import "../styles/Card.css";

const Card = ({
  children,
  variant = "default",
  padding = "medium",
  className = "",
  ...props
}) => {
  const cardClasses = [
    "card",
    `card-${variant}`,
    `card-padding-${padding}`,
    className
  ].filter(Boolean).join(" ");

  return (
    <div className={cardClasses} {...props}>
      {children}
    </div>
  );
};

export default Card;
