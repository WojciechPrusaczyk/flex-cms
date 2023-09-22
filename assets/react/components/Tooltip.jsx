import React, { useState } from "react";
export const Tooltip = (props) => {
	const [isTooltipShown, setIsTooltipShown] = useState(false);

	return (
		<React.Fragment>
			<img
				onClick={() => setIsTooltipShown(!isTooltipShown)}
				src="/build/icons/dashboard/tooltip.svg"
				alt="tooltip"
			/>
			{isTooltipShown && (
				<p id="tooltip-text">
					{props.text}
				</p>
			)}
		</React.Fragment>
	);
};
