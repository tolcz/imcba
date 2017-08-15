/**
 * Randomize array element order in-place.
 * Using Durstenfeld shuffle algorithm.
 */
function shuffleArray(array) {
	for (var i = array.length - 1; i > 0; i--) {
		var j = Math.floor(Math.random() * (i + 1));
		var temp = array[i];
		array[i] = array[j];
		array[j] = temp;
	}
	return array;
}


var past = ['I am known for validating information',
	'I gather as much information as I can before making a decision',
	'I like to reason things out.',
	'I like to think things through before making a decision',
	'I need to have proof before I commit to something.',
	'I need to understand the risks involved before committing to something.',
	'I need to verify as much information as I can before making a decision',
	'I reflect on the facts before making a decision.',
	'I tend to analyze things thoroughly before making a decision.',
	'I tend to think things through carefully.',
	'I usually reflect carefully on what I know before making a decision.',
	'I usually reflect carefully on what I know to see how it applies to the current situation.',
	'I weigh the evidence before coming to a conclusion',
	'Only when I have the facts and information do I feel comfortable making a decision',
	'When I don’t know something, I will seek out additional information before making a decision. '
];

var present = ['Being organized is important to me.',
	'I am driven towards order',
	'I am good at organizing the resources needed to get things done',
	'I am known for getting things done.',
	'I enjoy creating structure.',
	'I have a plan for the future',
	'I like to be prepared for my day.',
	'I like to plan my daily activities',
	'I manage others by organizing/prioritizing tasks. ',
	'I thrive in environments that are orderly and structured.',
	'It is important for me that things go according to plan.',
	'People think I am best at planning and organization.',
	'People think of me as a follow through kind of person',
	'People think of me as organized.',
	'People think of me as structured.'
];

var future = ['I am able to inspire others with my vision',
	'I am always on the lookout for new opportunities.',
	'I am driven to explore.',
	'I am known for generating ideas.',
	'I am known for invention/innovation.',
	'I am open to future possibilities.',
	'I am regarded as an agent of change.',
	'I can easily imagine all sorts of future possibilities',
	'I like to generate ideas.',
	'I manage others through inspiration.',
	'I thrive in environments that are flexible and dynamic.',
	'People think I am best at innovation and invention.',
	'People think of me as a visionary.',
	'People think of me as dynamic.',
	'People think of me as imaginative.'
];

shuffleArray(past);
shuffleArray(present);
shuffleArray(future);

document.getElementById("label_3").innerHTML  = past[0];
document.getElementById("label_6").innerHTML  = past[1];
document.getElementById("label_10").innerHTML = past[2];

document.getElementById("label_4").innerHTML  = present[0];
document.getElementById("label_7").innerHTML  = present[1];
document.getElementById("label_11").innerHTML = present[2];

document.getElementById("label_5").innerHTML  = future[0];
document.getElementById("label_8").innerHTML  = future[1];
document.getElementById("label_12").innerHTML = future[2];
